"""Exercise trust gates, secret preservation and failed-release recovery without Docker."""
import importlib.util
import json
import os
from pathlib import Path
import secrets
import subprocess
import tempfile
import unittest
from unittest.mock import patch


def load(name, filename):
    path = Path(__file__).resolve().parents[2] / "deploy/lightsail" / filename
    spec = importlib.util.spec_from_file_location(name, path)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


cd = load("pull_cd", "poll-deploy.py")
wait = load("wait_cd", "wait-for-release.py")
domain = load("domain_cd", "activate-domain.py")
SHA = "a" * 40
OLD = "b" * 40


def run_data(**changes):
    return {"id": 123, "head_sha": SHA, "status": "completed", "conclusion": "success",
            "event": "push", "head_branch": "main", "path": ".github/workflows/ci.yml",
            "head_repository": {"full_name": cd.REPO}, **changes}


class TrustGateTests(unittest.TestCase):
    def test_only_successful_main_push_from_this_repository_is_accepted(self):
        self.assertEqual(cd.approved_run({"workflow_runs": [run_data()]}), run_data())
        for changes in [
            {"conclusion": "failure"}, {"status": "in_progress"}, {"event": "pull_request"},
            {"head_branch": "dev"}, {"head_repository": {"full_name": "fork/project"}},
            {"head_repository": None}, {"head_sha": "--upload-pack=unexpected"},
            {"path": ".github/workflows/deploy.yml"}, {"id": -1},
        ]:
            with self.subTest(changes=changes):
                self.assertIsNone(cd.approved_run({"workflow_runs": [run_data(**changes)]}))

    def test_does_not_fall_back_to_an_older_success_when_latest_ci_failed(self):
        payload = {"workflow_runs": [run_data(conclusion="failure"), run_data()]}
        self.assertIsNone(cd.approved_run(payload))

    def test_environment_update_preserves_unrelated_secrets_and_refuses_newlines(self):
        secret = secrets.token_hex(16)
        original = "APP_KEY=" + secret + "\nDB_PASSWORD=" + secret + "\nAPP_IMAGE_TAG=old\n"
        changed = cd.replace_env(original, {"APP_IMAGE_TAG": SHA, "APP_DOMAIN": "midulcepos.shop"})
        self.assertIn("APP_KEY=" + secret + "\n", changed)
        self.assertIn("DB_PASSWORD=" + secret + "\n", changed)
        self.assertIn("APP_IMAGE_TAG=" + SHA + "\n", changed)
        with self.assertRaises(ValueError):
            cd.replace_env(original, {"APP_DOMAIN": "example.test\nAPP_DEBUG=true"})

    def test_atomic_secret_file_is_private(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / ".env"
            cd.atomic_write(path, "example")
            self.assertEqual(path.stat().st_mode & 0o777, 0o600)
            self.assertEqual(path.read_text(), "example")


class DeploymentRecoveryTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.state = self.root / "state"
        self.state.mkdir()
        self.previous_env = "APP_KEY=" + secrets.token_hex(16) + "\nAPP_IMAGE_TAG=previous\n"
        (self.root / ".env").write_text(self.previous_env)
        (self.root / ".deploy-status").mkdir()
        (self.root / ".deploy-status/release.json").write_text(json.dumps({"commit": OLD}))
        self.patches = [patch.object(cd, "ROOT", self.root), patch.object(cd, "STATE", self.state)]
        for item in self.patches:
            item.start()
            self.addCleanup(item.stop)

    def fake_command(self, args, log, capture=False):
        if args[:2] == ["git", "rev-parse"]:
            return SHA
        if args[:2] == ["git", "status"]:
            return ""
        return None

    def test_build_failure_restores_environment_without_replacing_live_containers(self):
        def fail_build(args, log, capture=False):
            if "build" in args:
                raise subprocess.CalledProcessError(1, args)
            return self.fake_command(args, log, capture)

        with patch.object(cd, "command", side_effect=fail_build) as command, patch.object(cd, "launch") as launch:
            with self.assertRaises(subprocess.CalledProcessError):
                cd.deploy(run_data(), {"commit": OLD})
        self.assertEqual((self.root / ".env").read_text(), self.previous_env)
        launch.assert_not_called()
        self.assertIn(unittest.mock.call(["git", "checkout", "--detach", OLD], unittest.mock.ANY), command.call_args_list)
        self.assertEqual(json.loads((self.state / "state.json").read_text())["failed_commit"], SHA)
        self.assertEqual(json.loads((self.root / ".deploy-status/release.json").read_text())["commit"], OLD)

    def test_health_failure_restarts_previous_application_and_does_not_publish_failed_sha(self):
        with patch.object(cd, "command", side_effect=self.fake_command), patch.object(cd, "launch") as launch, \
                patch.object(cd, "health", side_effect=[RuntimeError("unhealthy"), None]):
            with self.assertRaisesRegex(RuntimeError, "unhealthy"):
                cd.deploy(run_data(), {"commit": OLD})
        self.assertEqual(launch.call_count, 2)
        self.assertEqual((self.root / ".env").read_text(), self.previous_env)
        self.assertEqual(json.loads((self.root / ".deploy-status/release.json").read_text())["commit"], OLD)

    def test_publishes_new_sha_only_after_health_checks(self):
        def healthy(log):
            self.assertEqual(json.loads((self.root / ".deploy-status/release.json").read_text())["commit"], OLD)

        with patch.object(cd, "command", side_effect=self.fake_command), patch.object(cd, "launch"), \
                patch.object(cd, "health", side_effect=healthy):
            cd.deploy(run_data(), {"commit": OLD})
        release_path = self.root / ".deploy-status/release.json"
        self.assertEqual(json.loads(release_path.read_text())["commit"], SHA)
        self.assertEqual(release_path.stat().st_mode & 0o777, 0o644)
        self.assertEqual(json.loads((self.state / "state.json").read_text())["commit"], SHA)

    def test_superseded_commit_does_not_change_environment_or_containers(self):
        with patch.object(cd, "command", return_value="c" * 40), patch.object(cd, "launch") as launch:
            cd.deploy(run_data(), {"commit": OLD})
        self.assertEqual((self.root / ".env").read_text(), self.previous_env)
        launch.assert_not_called()

    def test_tracked_server_edits_are_not_overwritten(self):
        with patch.object(cd, "command", side_effect=[None, SHA, " M tracked-file"]), \
                patch.object(cd, "launch") as launch:
            with self.assertRaisesRegex(RuntimeError, "Tracked server edits"):
                cd.deploy(run_data(), {"commit": OLD})
        self.assertEqual((self.root / ".env").read_text(), self.previous_env)
        launch.assert_not_called()


class HostedVerifierTests(unittest.TestCase):
    def test_rejects_invalid_commit_before_making_requests(self):
        with patch.dict(os.environ, {"EXPECTED_SHA": "--invalid"}), patch.object(wait.urllib.request, "urlopen") as request:
            with self.assertRaisesRegex(SystemExit, "Invalid expected commit"):
                wait.main()
        request.assert_not_called()

    def test_previous_healthy_release_is_not_reported_as_current(self):
        import io
        response = io.BytesIO(json.dumps({"commit": OLD, "status": "healthy"}).encode())
        with patch.dict(os.environ, {"EXPECTED_SHA": SHA, "DEPLOY_URL": "https://example.test"}), \
                patch.object(wait.time, "monotonic", side_effect=[0, 0, 1501]), \
                patch.object(wait.time, "sleep"), patch.object(wait.urllib.request, "urlopen", return_value=response):
            with self.assertRaisesRegex(SystemExit, "did not publish"):
                wait.main()


class DomainDnsTests(unittest.TestCase):
    @staticmethod
    def response(answers):
        import io
        return io.BytesIO(json.dumps({"Answer": answers}).encode())

    def test_wrong_address_stops_domain_activation(self):
        response = self.response([{"type": 1, "data": "192.0.2.1"}])
        with patch.object(domain.urllib.request, "urlopen", return_value=response):
            with self.assertRaisesRegex(SystemExit, "DNS not ready"):
                domain.check_dns("example.test", "13.184.138.10")

    def test_conflicting_ipv6_stops_domain_activation(self):
        responses = [self.response([{"type": 1, "data": "13.184.138.10"}]),
                     self.response([{"type": 28, "data": "2001:db8::1"}])]
        with patch.object(domain.urllib.request, "urlopen", side_effect=responses):
            with self.assertRaisesRegex(SystemExit, "AAAA"):
                domain.check_dns("example.test", "13.184.138.10")

    def test_apex_and_www_resolving_to_server_are_accepted(self):
        responses = [self.response([{"type": 1, "data": "13.184.138.10"}]), self.response([]),
                     self.response([{"type": 5, "data": "example.test."}, {"type": 1, "data": "13.184.138.10"}]),
                     self.response([])]
        with patch.object(domain.urllib.request, "urlopen", side_effect=responses):
            domain.check_dns("example.test", "13.184.138.10")


if __name__ == "__main__":
    unittest.main()

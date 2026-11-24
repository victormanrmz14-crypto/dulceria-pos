"""Regenerate platform SVGs with fontTools (asset tooling only; not a runtime dependency).

Source font: public/brand/fonts/dm-sans-latin-variable.ttf, SIL OFL 1.1.
Run: python3 scripts/generate-brand.py
"""
from pathlib import Path
from fontTools.ttLib import TTFont
from fontTools.varLib.instancer import instantiateVariableFont
from fontTools.pens.svgPathPen import SVGPathPen

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "public/brand"
SYMBOL = 'M16 12H30C45 12 54 20 54 32S45 52 30 52H16ZM27 23V41H30C38 41 43 38 43 32S38 23 30 23Z M12 22L2 16L5 32L2 48L12 42Z M58 23L64 18L61 32L64 46L58 41Z'


def svg(name, box, contents, label):
    (OUT / name).write_text(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{box}" role="img" aria-labelledby="title"><title id="title">{label}</title>{contents}</svg>\n')


def mark(color):
    return f'<path fill="{color}" fill-rule="evenodd" d="{SYMBOL}"/>'


font = instantiateVariableFont(TTFont(OUT / "fonts/dm-sans-latin-variable.ttf"), {"wght": 700, "opsz": 24})
glyphs = font.getGlyphSet()
cmap = font.getBestCmap()
scale = 29 / font['head'].unitsPerEm
paths, cursor = [], 0
for letter in 'Dulcería POS':
    glyph = glyphs[cmap[ord(letter)]]
    pen = SVGPathPen(glyphs)
    glyph.draw(pen)
    paths.append(f'<path transform="translate({cursor:.3f} 0)" d="{pen.getCommands()}"/>')
    cursor += glyph.width
width = round(80 + cursor * scale + 2)
wordmark = f'<g transform="translate(80 43) scale({scale} -{scale})">{"".join(paths)}</g>'
for variant, color, ink in [('horizontal', '#8b243a', '#202126'), ('mono', '#202126', '#202126'), ('dark', '#ffffff', '#ffffff')]:
    svg(f'logo-{variant}.svg', f'0 0 {width} 64', mark(color) + f'<g fill="{ink}">{wordmark}</g>', 'Dulcería POS')
svg('symbol.svg', '0 0 64 64', mark('#8b243a'), 'Dulcería POS — símbolo')
svg('symbol-mono.svg', '0 0 64 64', mark('#202126'), 'Dulcería POS — símbolo monocromático')
svg('symbol-dark.svg', '0 0 64 64', mark('#ffffff'), 'Dulcería POS — símbolo para fondo oscuro')
svg('app-icon.svg', '0 0 108 108', '<path fill="#8b243a" d="M0 0H108V108H0Z"/>' + '<g transform="translate(22 22)">' + mark('#ffffff') + '</g>', 'Dulcería POS — icono adaptable')
svg('android-foreground.svg', '0 0 108 108', '<g transform="translate(22 22)">' + mark('#ffffff') + '</g>', 'Dulcería POS — capa de primer plano')
svg('favicon.svg', '0 0 64 64', '<rect width="64" height="64" rx="14" fill="#8b243a"/>' + '<g transform="translate(7 7) scale(.78)">' + mark('#ffffff') + '</g>', 'Dulcería POS')

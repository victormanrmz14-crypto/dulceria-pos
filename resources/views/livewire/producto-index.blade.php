<div x-data="productoIndexData({
        productos: @js($productos),
        editBase: '{{ url('productos') }}'
    })">
    <style>
        .producto-row--inactive {
            opacity: 0.5;
        }

        .stock-low {
            color: #dc3545;
        }

        .stock-ok {
            color: #28a745;
        }

        .producto-action {
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
        }

        .producto-action--desactivar {
            background: #dc3545;
        }

        .producto-action--activar {
            background: #28a745;
        }
    </style>

    {{-- Filtros --}}
    <div style="background:#fff; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:24px;
                display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">

        <div style="flex:1; min-width:200px;">
            <label style="display:block; font-size:0.82rem; font-weight:600;
                          color:#777; margin-bottom:6px;">Buscar producto</label>
            <input type="text"
                 x-model="buscar"
                   placeholder="Escribe para buscar..."
                   style="width:100%; padding:9px 14px; border:1px solid #ddd;
                          border-radius:8px; font-size:0.9rem; outline:none;">
        </div>

        <div style="min-width:180px;">
            <label style="display:block; font-size:0.82rem; font-weight:600;
                          color:#777; margin-bottom:6px;">Categoría</label>
            <select x-model="categoriaId"
                    style="width:100%; padding:9px 14px; border:1px solid #ddd;
                           border-radius:8px; font-size:0.9rem; outline:none; background:#fff;">
                <option value="">Todas</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>

        <button x-on:click="resetFiltros()"
                style="background:#f0f0f0; color:#555; padding:9px 20px;
                       border-radius:8px; border:none; font-weight:600;
                       font-size:0.9rem; cursor:pointer;">
            Limpiar
        </button>
    </div>

    {{-- Tabla --}}
    <div style="background:#fff; border-radius:12px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#8B0000; color:white;">
                    <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">#</th>
                    <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Nombre</th>
                    <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Categoría</th>
                    <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Marca</th>
                    <th style="padding:14px 20px; text-align:right; font-size:0.85rem;">Precio</th>
                    <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Stock</th>
                    <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Estado</th>
                    <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="producto in paginados" :key="producto.id">
                    <tr :class="!producto.activo ? 'producto-row--inactive' : ''"
                        style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px; color:#999; font-size:0.85rem;" x-text="producto.id"></td>
                        <td style="padding:14px 20px;">
                            <span style="font-weight:600; color:#333;" x-text="producto.nombre"></span>
                            <template x-if="stockBajo(producto) && producto.activo">
                                <span style="background:#fff3cd; color:#856404; padding:2px 8px;
                                             border-radius:20px; font-size:0.75rem; font-weight:600;
                                             margin-left:8px;">
                                    ⚠️ Stock bajo
                                </span>
                            </template>
                        </td>
                        <td style="padding:14px 20px; color:#666; font-size:0.9rem;" x-text="producto.categoria ?? '—'"></td>
                        <td style="padding:14px 20px; color:#666; font-size:0.9rem;" x-text="producto.marca ?? '—'"></td>
                        <td style="padding:14px 20px; text-align:right; font-weight:600; color:#333;" x-text="formatPrecio(producto.precio)"></td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :class="stockBajo(producto) ? 'stock-low' : 'stock-ok'" style="font-weight:600;" x-text="formatStock(producto.stock)"></span>
                            <span style="color:#aaa; font-size:0.8rem;" x-text="producto.unidad_medida"></span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <template x-if="producto.activo">
                                <span style="background:#d4edda; color:#155724; padding:4px 12px;
                                             border-radius:20px; font-size:0.8rem; font-weight:600;">
                                    Activo
                                </span>
                            </template>
                            <template x-if="!producto.activo">
                                <span style="background:#f8d7da; color:#721c24; padding:4px 12px;
                                             border-radius:20px; font-size:0.8rem; font-weight:600;">
                                    Inactivo
                                </span>
                            </template>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <a :href="`${editBase}/${producto.id}/edit`"
                               style="background:#f0a500; color:white; padding:6px 14px;
                                      border-radius:6px; text-decoration:none;
                                      font-size:0.82rem; font-weight:600; margin-right:6px;">
                                Editar
                            </a>
                            <button x-on:click="toggle(producto)"
                                    :class="producto.activo ? 'producto-action producto-action--desactivar' : 'producto-action producto-action--activar'">
                                <span x-text="producto.activo ? 'Desactivar' : 'Activar'"></span>
                            </button>
                        </td>
                    </tr>
                </template>

                <tr x-show="filtrados.length === 0">
                    <td colspan="8" style="padding:40px; text-align:center; color:#bbb;">
                        No se encontraron productos.
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Paginación --}}
        <div x-show="totalPaginas > 1" style="padding:16px 20px; border-top:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center;">
            <p style="font-size:0.85rem; color:#999; margin:0;" x-text="`Mostrando ${inicio + 1} - ${fin} de ${filtrados.length} productos`"></p>

            <div style="display:flex; gap:8px;">
                <button x-on:click="prev()" :disabled="pagina === 1"
                        style="padding:6px 14px; border-radius:6px; border:none; background:#f0f0f0; color:#555; font-weight:600; cursor:pointer;">
                    ← Anterior
                </button>
                <button x-on:click="next()" :disabled="pagina >= totalPaginas"
                        style="padding:6px 14px; border-radius:6px; border:none; background:#f0f0f0; color:#555; font-weight:600; cursor:pointer;">
                    Siguiente →
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function productoIndexData({ productos, editBase }) {
        return {
            buscar: '',
            categoriaId: '',
            pagina: 1,
            porPagina: 15,
            productos,
            editBase,

            get filtrados() {
                const q = this.buscar.trim().toLowerCase();

                return this.productos.filter((p) => {
                    const byCategoria = this.categoriaId === '' || String(p.categoria_id) === String(this.categoriaId);
                    const byNombre = q === '' || p.nombre.toLowerCase().includes(q);
                    return byCategoria && byNombre;
                });
            },

            get totalPaginas() {
                return Math.max(1, Math.ceil(this.filtrados.length / this.porPagina));
            },

            get inicio() {
                return (this.pagina - 1) * this.porPagina;
            },

            get fin() {
                return Math.min(this.inicio + this.porPagina, this.filtrados.length);
            },

            get paginados() {
                const rows = this.filtrados.slice(this.inicio, this.inicio + this.porPagina);

                if (this.pagina > this.totalPaginas) {
                    this.pagina = this.totalPaginas;
                }

                return rows;
            },

            resetFiltros() {
                this.buscar = '';
                this.categoriaId = '';
                this.pagina = 1;
            },

            next() {
                if (this.pagina < this.totalPaginas) {
                    this.pagina += 1;
                }
            },

            prev() {
                if (this.pagina > 1) {
                    this.pagina -= 1;
                }
            },

            formatPrecio(value) {
                return '$' + Number(value || 0).toFixed(2);
            },

            formatStock(value) {
                return Number(value || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 });
            },

            stockBajo(producto) {
                return Number(producto.stock) <= Number(producto.stock_minimo);
            },

            toggle(producto) {
                const msg = producto.activo ? '¿Desactivar este producto?' : '¿Activar este producto?';

                if (confirm(msg)) {
                    this.$wire.toggleActivo(producto.id);
                }
            }
        };
    }
</script>
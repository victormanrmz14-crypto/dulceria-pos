/** Existing Ventas/Ticket Inertia payload; amounts are supplied by Laravel. */
export interface TicketSale {
    id: number;
    folio: string;
    fecha: string;
    cajero: string;
    metodo_pago: string;
    total: number;
    monto_recibido: number | null;
    cambio: number | null;
    detalles: { producto: string; cantidad: number; precio_unitario: number; importe: number }[];
}

export interface TicketConfig {
    nombre?: string | null;
    logo?: string | null;
    rfc?: string | null;
    direccion?: string | null;
    telefono?: string | null;
    encabezado?: string | null;
    pie?: string | null;
}

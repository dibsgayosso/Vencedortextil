# Panel del propietario — El Vencedor

Actualizar main y ejecutar `php index.php migrate version 20261010031500` desde la raíz del POS.

Activar en Empleados → Permisos → Reportes: Ver panel del propietario. Ver diferencia en cortes de caja se asigna por separado. Con ambos permisos de panel, Home presenta el del propietario. Las sucursales se filtran por acceso y permiso específico.

Incluye ventas de mercancía por método de pago, entradas del día, créditos por cobrar y saldos a favor por separado, gastos por categoría, última venta y último corte. Las ventas excluyen abonos a crédito para no contabilizarlos nuevamente como ventas; el supervisor sí incluye transferencias de dichos abonos. Actualización cada 30 segundos.

La autorización de mercancía y las notificaciones push aún no están habilitadas en este repositorio.

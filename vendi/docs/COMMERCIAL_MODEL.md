# Modelo comercial
Cada cuenta incluye una sucursal base según el plan. Las sucursales adicionales son add-ons cobrables.
Flujo: crear sucursal -> pending -> registrar add-on/pago -> emitir token remoto -> activar -> active.
El servidor de licencias será independiente de la base transaccional del cliente. La aplicación validará licencia periódicamente y conservará un periodo de gracia configurable para evitar detener una caja por una caída temporal de internet.
La tarifa NO se codifica en el POS; se obtiene del plan comercial para poder cambiar precios sin desplegar software.

# Panel del supervisor — El Vencedor

Actualizar desde main los archivos del commit e instalar la migración:
`php index.php migrate version 20261010030000`

En Empleados → Editar → Permisos → Reportes, activar “Ver panel del supervisor” para las sucursales correspondientes.

El panel muestra transferencias de ventas y abonos a crédito, número de operaciones por método, última venta de mercancía y gastos con impuestos. Se actualiza cada 30 segundos. “Operaciones” abre el detalle de hoy, con 50 filas por página y validación de permiso por sucursal. Las ventas combinadas muestran solo la parte de transferencia. Cada sucursal usa su zona horaria. Los demás usuarios conservan su Home habitual.

Este repositorio no tiene el flujo de autorización de mercancía ni push de SAMYPOS. El panel lo muestra explícitamente como “Por activar”; esta migración solo agrega el permiso de consulta y no modifica inventarios, recepciones ni permisos existentes.

# Seguridad Vendi POS
- Toda consulta transaccional debe filtrar business_id y, cuando aplique, branch_id.
- Las sucursales nuevas nacen pending y no pueden operar hasta activación remota.
- Nunca almacenar tokens de activación en texto plano; sólo SHA-256.
- CSD, llaves fiscales y secretos API deben cifrarse fuera del webroot.
- Usar password_hash/password_verify, CSRF, cookies HttpOnly/Secure/SameSite y sesiones regeneradas.
- Cancelaciones, devoluciones, descuentos, cambios de precio y activaciones deben generar auditoría.
- Una suspensión remota no debe borrar información local: sólo impedir nuevas operaciones comerciales conforme al contrato.

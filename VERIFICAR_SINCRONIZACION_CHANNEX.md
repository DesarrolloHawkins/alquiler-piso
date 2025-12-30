# 🔍 Guía para Verificar la Sincronización con Channex

## 📋 Resumen

Cuando se crea una reserva desde la web, el sistema ahora:
1. ✅ Crea la reserva en la base de datos
2. ✅ Sincroniza automáticamente con Channex para bloquear la disponibilidad
3. ✅ Registra logs detallados de cada operación

## 🧪 Cómo Verificar que Funciona

### 1. Usar el Comando de Prueba

```bash
# Probar con la última reserva web creada
php artisan test:channex-sync

# Probar con una reserva específica
php artisan test:channex-sync --reserva_id=5149

# Probar con datos manuales
php artisan test:channex-sync --apartamento_id=4 --fecha_entrada=2025-12-24 --fecha_salida=2025-12-26
```

### 2. Revisar los Logs

Los logs se guardan en `storage/logs/laravel.log`. Busca:

**✅ Sincronización exitosa:**
```
[INFO] ✅ Reserva web sincronizada exitosamente con Channex - Disponibilidad bloqueada
```

**❌ Errores:**
```
[ERROR] ❌ Error al sincronizar reserva web con Channex
```

### 3. Verificar en Channex

1. Accede a tu panel de Channex
2. Ve a la sección de disponibilidad
3. Verifica que las fechas de la reserva web estén bloqueadas (disponibilidad = 0)

### 4. Verificar Variables de Entorno

Asegúrate de que en tu `.env` tengas:

```env
CHANNEX_URL=https://app.channex.io/api/v1
CHANNEX_TOKEN=tu_token_aqui
```

## 🔍 Qué Verificar en los Logs

### Logs de Éxito

Busca en los logs estas líneas después de crear una reserva web:

```
[INFO] Intentando sincronizar reserva web con Channex
  - reserva_id: X
  - codigo_reserva: WEB-XXXXX
  - apartamento_id_channex: Y
  - room_type_id_channex: Z
  - payload: {...}

[INFO] ✅ Reserva web sincronizada exitosamente con Channex
  - channex_response: {...}
```

### Logs de Error

Si hay errores, verás:

```
[ERROR] ❌ Error al sincronizar reserva web con Channex
  - http_status: 400/401/500
  - error_body: "..."
  - payload_enviado: {...}
```

### Errores Comunes

1. **Token no configurado:**
   ```
   [ERROR] Channex API token no configurado en .env (CHANNEX_TOKEN)
   ```
   **Solución:** Añade `CHANNEX_TOKEN=tu_token` en `.env`

2. **Faltan datos de Channex:**
   ```
   [WARNING] No se puede sincronizar con Channex: faltan datos
   ```
   **Solución:** Verifica que el apartamento tenga `id_channex` y el RoomType tenga `id_channex`

3. **Error HTTP:**
   ```
   [ERROR] http_status: 401
   ```
   **Solución:** Verifica que el token sea válido y tenga permisos

## 📊 Payload que se Envía a Channex

```json
{
  "values": [
    {
      "property_id": "ID_CHANNEX_APARTAMENTO",
      "room_type_id": "ID_CHANNEX_ROOM_TYPE",
      "date_from": "2025-12-24",
      "date_to": "2025-12-25",
      "update_type": "availability",
      "availability": 0
    }
  ]
}
```

## ✅ Checklist de Verificación

- [ ] Variables de entorno configuradas (`CHANNEX_URL`, `CHANNEX_TOKEN`)
- [ ] Apartamentos tienen `id_channex` configurado
- [ ] RoomTypes tienen `id_channex` configurado
- [ ] Logs muestran sincronización exitosa
- [ ] Disponibilidad bloqueada en Channex para las fechas de reserva
- [ ] No hay errores en los logs después de crear reservas web

## 🐛 Debugging

Si la sincronización no funciona:

1. **Ejecuta el comando de prueba:**
   ```bash
   php artisan test:channex-sync --reserva_id=ULTIMA_RESERVA_WEB
   ```

2. **Revisa los logs detallados:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i channex
   ```

3. **Verifica la respuesta de Channex:**
   - Los logs incluyen la respuesta completa de Channex
   - Busca `channex_response` en los logs de éxito

4. **Verifica la configuración:**
   ```bash
   php artisan tinker
   >>> env('CHANNEX_TOKEN')
   >>> env('CHANNEX_URL')
   ```

## 📝 Notas Importantes

- La sincronización se ejecuta **inmediatamente** después de crear la reserva web (antes del pago)
- Si la sincronización falla, **NO se cancela la reserva** (solo se loguea el error)
- La disponibilidad se bloquea con `availability: 0` para las fechas de la reserva
- La fecha de salida se ajusta automáticamente (se resta 1 día) según la API de Channex


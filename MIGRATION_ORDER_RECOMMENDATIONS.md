# RECOMENDACIONES DE ORDEN DE MIGRACIONES

## ✅ PROBLEMAS CORREGIDOS

### 1. Referencias de tablas incorrectas
- ✅ `85_create_apartamento_item_checklist.php`: Corregida referencia a `apartamento_limpieza`
- ✅ `40_create_estados_mensajes.php`: Corregido método `down()`
- ✅ `43_update_chatgpt.php`: Agregadas columnas faltantes
- ✅ `38_update_huesped.php`: Corregido método `down()`
- ✅ `37_update_huesped.php`: Corregido método `down()`
- ✅ `21_update_user.php`: Corregido método `down()`
- ✅ `22_update_ photos.php`: Corregido método `down()`
- ✅ `23_update_photos.php`: Corregido método `down()`
- ✅ `2025_05_09_110459_add_respuesta_id_to_chatgpt_table.php`: Corregido método `down()`

## 📋 ORDEN RECOMENDADO DE EJECUCIÓN

### FASE 1: Tablas Base (Sin dependencias)
1. `01_create_users_table.php`
2. `07_create_apartamentos.php`
3. `08_create_estado.php`
4. `09_create_clientes.php`
5. `11_create_estado_realizar_apartamento.php`
6. `13_create_photo_categoria.php`
7. `27_create_mensaje_auto_categoria.php`
8. `44_create_configuracion.php`
9. `46_create_reparaciones.php`
10. `47_create_fichajes.php`
11. `48_create_pausas.php`
12. `49_create_bancos.php`
13. `50_create_categoria_gastos.php`
14. `52_update_gastos.php` (crea estados_gastos)
15. `54_create_estados_ingresos.php`
16. `55_create_categoria_ingresos.php`
17. `58_create_cuentas_contable.php`
18. `59_create_grupo_contable.php`
19. `60_create_sub_cuentas_contable.php`
20. `61_create_sub_cuenta_hija.php`
21. `62_create_sub_grupo_contable.php`
22. `63_create_formas_pago.php`
23. `67_create_anio.php`
24. `70_create_estados_diario.php`
25. `72_create_prompt_asistente.php`
26. `73_create_email_notificaciones.php`
27. `77_create_edificio.php`
28. `79_limpieza_fondo.php`
29. `84_create_proveedores.php`
30. `86_create_invoices_status.php`
31. `88_create_invoice_reference_autoincrement.php`
32. `90_create_temporadas.php`
33. `93_create_emails.php`
34. `94_create_status_email.php`
35. `95_create_category_email.php`
36. `99_create_photo_requirements.php`
37. `100_create_hash_movimientos.php`
38. `111_create_ari_updates.php`
39. `116_create_webhook_categories.php`
40. `120_create_holidays_status.php`
41. `2025_05_08_092824_create_whatsapp_templates_table.php`
42. `2025_05_08_194555_create_whatsapp_logs_table.php`
43. `2025_05_08_194521_create_whatsapp_mensajes_table.php`
44. `2025_04_08_151536_create_mensajes_table.php`

### FASE 2: Tablas con dependencias simples
45. `10_create_reservas.php` (depende de: clientes, apartamentos, estados)
46. `12_create_realizar_apartamento.php` (depende de: apartamentos, apartamento_estado, reservas)
47. `14_create_photos.php` (depende de: apartamento_limpieza, photo_categoria)
48. `25_create_chat_gpt.php` (sin dependencias)
49. `28_create_mensaje_auto.php` (depende de: reservas, clientes, mensajes_auto_categorias)
50. `29_create_huesped.php` (depende de: reservas)
51. `51_create_gasto.php` (depende de: categoria_gastos, bank_accounts)
52. `56_create_ingresos.php` (depende de: categoria_ingresos, estados_ingresos, bank_accounts)
53. `64_create_diario_caja.php` (depende de: gastos, ingresos, formas_pago)
54. `76_create_limpiadora_guardia.php` (depende de: users)
55. `80_create_checklists.php` (depende de: edificios, apartamento_limpieza)
56. `81_create_items_checklists.php` (depende de: checklists)
57. `82_create_controles_limpieza.php` (depende de: apartamentos, apartamento_limpieza, items_checklists)
58. `85_create_apartamento_item_checklist.php` (depende de: apartamento_limpieza, items_checklists)
59. `87_create_invoices.php` (sin foreign keys)
60. `91_create_tarifas.php` (depende de: temporadas)
61. `92_create_invoice_concepts.php` (depende de: invoices)
62. `97_create_checklist_photo_requirements.php` (depende de: checklists)
63. `105_create_room_types.php` (depende de: apartamentos)
64. `106_create_rate_plans.php` (depende de: apartamentos, room_types)
65. `107_create_rate_plan_options.php` (depende de: rate_plans)
66. `109_create_apartamento_photos.php` (depende de: apartamentos)
67. `113_create_presupuestos.php` (depende de: clientes)
68. `114_create_presupuesto_conceptos.php` (depende de: presupuestos)
69. `117_create_webhooks.php` (depende de: apartamentos, webhook_categories)
70. `118_create_holidays.php` (depende de: users)
71. `119_create_holidays_additions.php` (depende de: users)
72. `121_create_holidays_petition.php` (depende de: users, holidays_status)
73. `2025_03_13_104959_create_metalicos_table.php` (depende de: reservas)
74. `2025_05_08_195517_create_whatsapp_estado_mensajes_table.php` (depende de: whatsapp_mensajes)

### FASE 3: Actualizaciones de tablas existentes
75. `15_create_clientes_update.php`
76. `19_update_photo.php`
77. `20_update_realizar_table.php`
78. `30_update_photos.php`
79. `41_update_apartamentos_edificio.php`
80. `42_update_apartamentos.php`
81. `43_update_chatgpt.php`
82. `45_update_configuracion.php`
83. `53_create_estados_gastos.php`
84. `57_update_reservas.php`
85. `65_update_limpieza.php`
86. `66_update_user.php`
87. `68_update_anio.php`
88. `69_update_anio.php`
89. `71_update_diario_caja.php`
90. `74_update_tecnico.php`
91. `75_update_tecnico.php`
92. `78_update_apartamentos.php`
93. `83_update_limpieza.php`
94. `89_update_invoices.php`
95. `96_update_email.php`
96. `98_update_photo.php`
97. `101_update_reserva.php`
98. `102_update_email.php`
99. `103_update_cliente.php`
100. `104_update_apartamento.php`
101. `107_update_apartamentos.php`
102. `108_update_apartamentos_table_add_missing_fields.php`
103. `112_create_add_additional_fields_to_clientes_and_huespedes.php`
104. `115_update_presupuesto_conceptos_add_dates_and_prices.php`
105. `2025_02_25_150354_update_reserva.php`
106. `2025_02_26_103312_update_reserva_rate copy.php`
107. `2025_02_26_103315_update_reserva_rate.php`
108. `2025_03_18_165206_add_tipo_to_metalicos_table.php`
109. `2025_03_18_165535_add_observaciones_to_metalicos_table.php`
110. `2025_04_08_155230_add_openai_thread_id_to_mensaje_chats_table.php`
111. `2025_05_08_094413_whatsapp_template_update.php`
112. `2025_05_08_195457_add_whatsapp_mensaje_id_to_whatsapp_mensaje_chatgpt_table.php`
113. `2025_05_09_110459_add_respuesta_id_to_chatgpt_table.php`
114. `2025_05_09_113756_add_reply_to_id_to_whatsapp_mensajes_table.php`
115. `2025_06_03_135453_make_id_reserva_nullable_in_apartamento_limpieza_items_table.php`
116. `2025_06_16_150209_add_fecha_to_presupuestos_table.php`
117. `2025_06_16_151352_add_estado_to_presupuestos_table.php`

## ⚠️ ADVERTENCIAS IMPORTANTES

### 1. Migraciones con fechas futuras
Las migraciones con fechas de 2025 se ejecutarán después de las numeradas. Considera renombrarlas con fechas actuales.

### 2. Migraciones duplicadas
- `65_update_limpieza.php` y `83_update_limpieza.php` son idénticas
- `2025_02_26_103312_update_reserva_rate copy.php` parece ser una copia

### 3. Migraciones faltantes
Algunas migraciones referenciadas no existen:
- `16_update_photo.php`
- `17_update_photo.php`
- `18_update_photo.php`
- `24_create_mensaje_auto_categoria.php`
- `26_create_mensaje_auto_categoria.php`
- `31_update_photos.php`
- `32_update_photos.php`
- `33_update_photos.php`
- `34_update_photos.php`
- `35_update_photos.php`
- `36_update_photos.php`
- `39_update_photos.php`

## 🔧 ACCIONES RECOMENDADAS

1. **Ejecutar migraciones en el orden especificado**
2. **Renombrar migraciones con fechas futuras**
3. **Eliminar migraciones duplicadas**
4. **Verificar que todas las migraciones referenciadas existan**
5. **Hacer backup antes de ejecutar las migraciones**
6. **Probar en un entorno de desarrollo primero**

## 📝 NOTAS ADICIONALES

- Todas las foreign keys están correctamente definidas
- Los métodos `down()` han sido corregidos
- Las referencias de tablas han sido verificadas
- El orden respeta las dependencias entre tablas 

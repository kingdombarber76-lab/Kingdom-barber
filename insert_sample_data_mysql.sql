-- Insertar datos de ejemplo en las tablas

-- 1. Insertar un Tenant (Negocio/Barbería)
INSERT INTO tenants (name, slug, phone, whatsapp, address, city, country, logo_url, rating, review_count, is_active)
VALUES 
('Barbería Premium', 'barberia-premium', '+595981234567', '+595981234567', 'Calle Principal 123', 'Asunción', 'PY', NULL, 5.0, 0, 1),
('Barbería Central', 'barberia-central', '+595982654321', '+595982654321', 'Av. Mariano Roque Alonso 456', 'San Juan', 'PY', NULL, 4.8, 15, 1);

-- 2. Insertar Usuarios (Propietarios, Barberos, Clientes)
-- Propietarios
INSERT INTO users (tenant_id, name, email, phone, password, role, is_active)
VALUES 
(1, 'Juan López', 'juan@barberia1.com', '+595981234567', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'owner', 1),
(2, 'Carlos Martín', 'carlos@barberia2.com', '+595982654321', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'owner', 1);

-- Barberos
INSERT INTO users (tenant_id, name, email, phone, password, role, is_active)
VALUES 
(1, 'Pedro González', 'pedro@barberia1.com', '+595981111111', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'barber', 1),
(1, 'Miguel Fernández', 'miguel@barberia1.com', '+595981222222', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'barber', 1),
(2, 'Roberto Díaz', 'roberto@barberia2.com', '+595982111111', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'barber', 1);

-- Clientes
INSERT INTO users (tenant_id, name, email, phone, password, role, is_active)
VALUES 
(1, 'Antonio Ruiz', 'antonio@email.com', '+595989999999', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'client', 1),
(1, 'Luis Rodríguez', 'luis@email.com', '+595988888888', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'client', 1),
(2, 'Fernando Gómez', 'fernando@email.com', '+595987777777', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'client', 1);

-- 3. Insertar Servicios
INSERT INTO services (tenant_id, name, description, duration_minutes, price, is_featured, is_active)
VALUES 
(1, 'Corte Clásico', 'Corte de cabello clásico con tijera', 30, 50000, 1, 1),
(1, 'Corte Fade', 'Corte moderno con degradado', 35, 60000, 1, 1),
(1, 'Afeitado', 'Afeitado con espuma caliente', 20, 35000, 0, 1),
(1, 'Barba + Corte', 'Corte y perfilado de barba', 45, 85000, 0, 1),
(2, 'Corte Premium', 'Corte de cabello con técnica avanzada', 40, 75000, 1, 1),
(2, 'Teñido', 'Teñido de cabello', 60, 100000, 0, 1);

-- 4. Insertar Horarios (Schedules)
-- Horarios para Pedro en la Barbería Premium (Lunes a Viernes)
INSERT INTO schedules (tenant_id, user_id, day_of_week, opens_at, closes_at, is_active)
VALUES 
(1, 3, 1, '08:00:00', '18:00:00', 1), -- Lunes
(1, 3, 2, '08:00:00', '18:00:00', 1), -- Martes
(1, 3, 3, '08:00:00', '18:00:00', 1), -- Miércoles
(1, 3, 4, '08:00:00', '18:00:00', 1), -- Jueves
(1, 3, 5, '08:00:00', '17:00:00', 1), -- Viernes
(1, 3, 6, '09:00:00', '15:00:00', 1); -- Sábado

-- Horarios para Miguel
INSERT INTO schedules (tenant_id, user_id, day_of_week, opens_at, closes_at, is_active)
VALUES 
(1, 4, 1, '09:00:00', '19:00:00', 1),
(1, 4, 2, '09:00:00', '19:00:00', 1),
(1, 4, 3, '09:00:00', '19:00:00', 1),
(1, 4, 4, '09:00:00', '19:00:00', 1),
(1, 4, 5, '09:00:00', '18:00:00', 1),
(1, 4, 6, '10:00:00', '16:00:00', 1);

-- 5. Insertar Citas (Appointments)
INSERT INTO appointments (id, tenant_id, service_id, barber_id, client_id, client_name, client_phone, appointment_date, start_time, end_time, status, total_price)
VALUES 
(UUID(), 1, 1, 3, 6, NULL, NULL, '2025-07-05', '09:00:00', '09:30:00', 'pending', 50000),
(UUID(), 1, 2, 3, 7, NULL, NULL, '2025-07-05', '10:00:00', '10:35:00', 'confirmed', 60000),
(UUID(), 1, 4, 4, NULL, 'Cliente Anónimo', '+595981111111', '2025-07-06', '14:00:00', '14:45:00', 'pending', 85000);

-- 6. Insertar Promociones
INSERT INTO promotions (tenant_id, title, description, starts_at, ends_at, code, discount_percent, is_active)
VALUES 
(1, 'Descuento 20% en Cortes', 'Disfruta de 20% de descuento en todos los cortes', '2025-07-01', '2025-07-31', 'CORTE20', 20.00, 1),
(1, 'Combo Barba + Corte', 'Combo especial a precio reducido', '2025-07-01', '2025-07-31', 'COMBO15', 15.00, 1),
(2, 'Promoción de Verano', 'Descuento en servicios seleccionados', '2025-07-01', '2025-08-31', 'VERANO25', 25.00, 1);

-- 7. Insertar Push Tokens (para notificaciones)
INSERT INTO push_tokens (tenant_id, user_id, token, platform, is_active)
VALUES 
(1, 6, 'token_ios_user6_xyz123', 'ios', 1),
(1, 7, 'token_android_user7_abc456', 'android', 1),
(2, 9, 'token_web_user9_def789', 'web', 1);

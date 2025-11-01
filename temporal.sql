CREATE TABLE control_mermas (

    
    -- RELACIONES CLAVE
    id_control_almacen INT NOT NULL,
    id_requisicion INT NOT NULL,
    id_cotizacion INT NULL,
    lote_pedimento VARCHAR(100) NOT NULL,
    
    -- DATOS DE ENTRADA (del operador CNC)
    pz_estimadas_sistema INT,           -- Piezas que deberían salir (teóricas)
    pz_maquinadas_reales INT NOT NULL,  -- Piezas que realmente salieron
    mm_entrega DECIMAL(10,2) NOT NULL,  -- Material entregado
    mm_usados_reales DECIMAL(10,2) NOT NULL, -- Material utilizado
    mm_retorno_real DECIMAL(10,2) NOT NULL,  -- Material recuperado
    scrap_pz INT DEFAULT 0,             -- Piezas defectuosas
    scrap_mm DECIMAL(10,2) DEFAULT 0,   -- Material defectuoso
    
    -- CÁLCULOS AUTOMÁTICOS (para reportes)
    merma_teorica_calculada DECIMAL(10,2),  -- (altura_sello + desbaste) * pz_reales
    merma_real_calculada DECIMAL(10,2),     -- mm_entrega - mm_usados - mm_retorno
    diferencia_merma DECIMAL(10,2),         -- merma_real - merma_teorica
    porcentaje_merma DECIMAL(5,2),          -- (merma_real / mm_entrega) * 100
    eficiencia_material DECIMAL(5,2),       -- (pz_reales * altura_sello) / mm_usados * 100
    
    -- METADATOS PARA FILTROS
    material VARCHAR(100),
    perfil_sello VARCHAR(50),
    altura_sello DECIMAL(8,2),
    tipo_desbaste DECIMAL(4,2),          -- 2.0 o 2.5 según material
    operador_cnc VARCHAR(100),
    
    -- CONTROL DE CALIDAD
    justificacion_operador TEXT,
    revisado_por VARCHAR(100),
    estado_revision ENUM('pendiente', 'aprobada', 'revisada', 'rechazada') DEFAULT 'pendiente',
    observaciones_supervisor TEXT,
    
    -- AUDITORÍA
    fecha_maquinado DATE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
);
CREATE OR REPLACE VIEW VISTA_CONDUCTORES_DISPONIBILIDAD AS
SELECT
    c.id_conductor,
    c.nombre,
    c.direccion,
    c.fotografia,
    c.id_usuario,
    g.nombre_genero,
    n.nombre_nacionalidad,
    u.estado AS estado_usuario,
    GROUP_CONCAT(DISTINCT t.numero SEPARATOR ', ') AS telefono, -- Restored telefono
    CASE
        WHEN EXISTS (SELECT 1 FROM SERVICIO s WHERE s.id_conductor = c.id_conductor AND s.estado IN ('asignado', 'en_ruta')) THEN 'Ocupado'
        ELSE 'Disponible'
    END AS disponibilidad,
    (SELECT MAX(s2.fecha_fin) FROM SERVICIO s2 WHERE s2.id_conductor = c.id_conductor AND s2.estado IN ('asignado', 'en_ruta')) AS disponible_en
FROM
    CONDUCTOR c
JOIN
    USUARIO u ON c.id_usuario = u.id_usuario
JOIN
    CAT_GENERO g ON c.id_genero = g.id_genero
JOIN
    CAT_NACIONALIDAD n ON c.id_nacionalidad = n.id_nacionalidad
LEFT JOIN
    TELEFONO t ON c.id_conductor = t.id_conductor -- Restored LEFT JOIN TELEFONO t
GROUP BY
    c.id_conductor,
    c.nombre,
    c.direccion,
    c.fotografia,
    c.id_usuario,
    g.nombre_genero,
    n.nombre_nacionalidad,
    u.estado;
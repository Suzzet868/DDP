-- Requiere que reportajes.autor_id ya exista y tenga FK hacia autores.id.
-- Ejecutar con respaldo de la base de datos.

START TRANSACTION;

CREATE TEMPORARY TABLE autor_map (
  id INT UNSIGNED NOT NULL PRIMARY KEY,
  canonical_id INT UNSIGNED NOT NULL,
  KEY idx_canonical_id (canonical_id)
);

INSERT INTO autor_map (id, canonical_id)
SELECT autor.id, MIN(canonico.id)
FROM autores AS autor
INNER JOIN autores AS canonico
  ON LOWER(TRIM(canonico.nombres)) = LOWER(TRIM(autor.nombres))
 AND COALESCE(LOWER(TRIM(canonico.ap_paterno)), '') = COALESCE(LOWER(TRIM(autor.ap_paterno)), '')
GROUP BY autor.id;

UPDATE reportajes AS reportaje
INNER JOIN autor_map AS mapa ON mapa.id = reportaje.autor_id
SET reportaje.autor_id = mapa.canonical_id
WHERE reportaje.autor_id <> mapa.canonical_id;

DELETE autor
FROM autores AS autor
INNER JOIN autor_map AS mapa ON mapa.id = autor.id
WHERE mapa.id <> mapa.canonical_id;

DROP TEMPORARY TABLE autor_map;

UPDATE autores SET ap_paterno = '' WHERE ap_paterno IS NULL;

ALTER TABLE autores
  MODIFY ap_paterno VARCHAR(100) NOT NULL DEFAULT '';

-- La restricción evita que vuelvan a registrarse personas repetidas.
ALTER TABLE autores
  DROP INDEX uq_autor_persona,
  ADD UNIQUE KEY uq_autor_persona (nombres, ap_paterno);

COMMIT;

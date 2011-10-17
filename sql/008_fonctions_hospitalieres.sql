DROP TABLE IF EXISTS fonctions_hospitalieres;
CREATE TABLE fonctions_hospitalieres (
    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(32),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO fonctions_hospitalieres (id, nom) VALUES (1, 'Aucune');
INSERT INTO fonctions_hospitalieres (id, nom) VALUES (2, 'A');
INSERT INTO fonctions_hospitalieres (id, nom) VALUES (3, 'B');
INSERT INTO fonctions_hospitalieres (id, nom) VALUES (4, 'C');

/*** creation de la table user */

CREATE TABLE utilisateur
(
    id INT PRIMARY KEY NOT NULL,
    firstName VARCHAR(100),
    lastName VARCHAR(100),
    email VARCHAR(255),
    password VARCHAR(64),
    role JSON,
)

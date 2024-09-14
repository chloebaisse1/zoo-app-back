CREATE TABLE Contact (
    id INT PRIMARY KEY NOT NULL,
    nom VARCHAR(255),
    email VARCHAR(255),
    demande TEXT,
    createdAt DATETIME,
)
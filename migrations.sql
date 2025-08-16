

-- Disabilita i controlli delle chiavi esterne temporaneamente per facilitare la creazione delle tabelle in caso di dipendenze incrociate
SET FOREIGN_KEY_CHECKS = 0;

-- Tabella per le tipologie di media (es. "Podcast", "Articolo", "Video")
CREATE TABLE IF NOT EXISTS media_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabella per le autrici
CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabella per i contenuti multimediali
CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    media_type_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (media_type_id) REFERENCES media_types(id) ON DELETE CASCADE
);

-- Tabella pivot per la relazione molti-a-molti tra contenuti e autrici
CREATE TABLE IF NOT EXISTS content_authors (
    content_id INT NOT NULL,
    author_id INT NOT NULL,
    PRIMARY KEY (content_id, author_id), -- Chiave primaria composita
    FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);

-- Riabilita i controlli delle chiavi esterne
SET FOREIGN_KEY_CHECKS = 1;

-- Esempi di dati iniziali 
INSERT IGNORE INTO media_types (name) VALUES
('Video'),
('Podcast'),
('Articolo'),
('Intervista');

INSERT IGNORE INTO authors (name, surname) VALUES
('Anna', 'Rossi'),
('Maria', 'Bianchi'),
('Giulia', 'Verdi');



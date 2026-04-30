-- Opravimto.sk – databázová schéma
-- Spustite: mysql -u root -p opravimto < schema.sql

CREATE DATABASE IF NOT EXISTS opravimto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE opravimto;

-- -------------------------------------------------------
-- Zákazníci
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS pouzivatelia (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    meno        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    heslo       VARCHAR(255) NOT NULL,
    telefon     VARCHAR(20),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Admini / technici
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS admini (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    meno     VARCHAR(100) NOT NULL,
    email    VARCHAR(150) NOT NULL UNIQUE,
    heslo    VARCHAR(255) NOT NULL
);

-- -------------------------------------------------------
-- Objednávky
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS objednavky (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id           VARCHAR(20) NOT NULL UNIQUE,
    pouzivatel_id       INT,
    zakaznik_meno       VARCHAR(100) NOT NULL,
    zakaznik_email      VARCHAR(150) NOT NULL,
    zakaznik_telefon    VARCHAR(20),
    zariadenie_typ      VARCHAR(100) NOT NULL,
    zariadenie_model    VARCHAR(150) NOT NULL,
    problem_nazov       VARCHAR(255) NOT NULL,
    problem_popis       TEXT,
    problem_kategoria   VARCHAR(50),
    priorita            TINYINT(1) DEFAULT 0,
    doprava_typ         ENUM('osobne','packeta') DEFAULT 'osobne',
    stav                ENUM('caka','diagnostika','oprava','testovanie','hotovo','zrusena') DEFAULT 'caka',
    cena_od             DECIMAL(8,2),
    cena_do             DECIMAL(8,2),
    finalna_cena        DECIMAL(8,2),
    poznamka_technika   TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pouzivatel_id) REFERENCES pouzivatelia(id) ON DELETE SET NULL
);

-- -------------------------------------------------------
-- História stavov
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS stav_historia (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    objednavka_id   INT NOT NULL,
    stav            VARCHAR(50) NOT NULL,
    poznamka        TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (objednavka_id) REFERENCES objednavky(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Správy (zákazník ↔ technik)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS spravy (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    objednavka_id   INT NOT NULL,
    od_admina       TINYINT(1) DEFAULT 0,
    sprava          TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (objednavka_id) REFERENCES objednavky(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Vzorové dáta
-- -------------------------------------------------------

-- Admin účet: admin@opravimto.sk / Admin123!
INSERT INTO admini (meno, email, heslo) VALUES
('Správca', 'admin@opravimto.sk', '$2y$12$YKjT5oUdz4X3J8P1N9W5.u6K.FZ6QxqoiN4h/g0CdH0q/0QGNB7oG');

-- Testovacie objednávky
INSERT INTO pouzivatelia (meno, email, heslo, telefon) VALUES
('Jozef Mináč',   'jozef@email.sk',  '$2y$12$dummy1hashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '+421 900 111 222'),
('Anna Kováčová', 'anna@email.sk',   '$2y$12$dummy2hashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '+421 900 333 444'),
('Martin Procházka', 'martin@email.sk', '$2y$12$dummy3hashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '+421 900 555 666');

INSERT INTO objednavky
    (ticket_id, pouzivatel_id, zakaznik_meno, zakaznik_email, zakaznik_telefon,
     zariadenie_typ, zariadenie_model, problem_nazov, problem_popis, problem_kategoria,
     priorita, doprava_typ, stav, cena_od, cena_do)
VALUES
('OPR-1042-JM', 1, 'Jozef Mináč', 'jozef@email.sk', '+421 900 111 222',
 'Smartfón', 'iPhone 13 Pro', 'Prasknutý displej – expresné vybavenie',
 'Displej je kompletne rozbitý po páde. Zariadenie sa zapína, ale dotyková vrstva nereaguje.',
 'prasknuty_displej', 1, 'osobne', 'oprava', 80.00, 150.00),

('OPR-1041-AK', 2, 'Anna Kováčová', 'anna@email.sk', '+421 900 333 444',
 'Notebook', 'MacBook Pro 16"', 'Výmena batérie',
 'Batéria drží maximálne 30 minút. Kapacita klesla na 41 %. Zariadenie je inak funkčné.',
 'problem_bateria', 0, 'packeta', 'diagnostika', 120.00, 200.00),

('OPR-1040-MP', 3, 'Martin Procházka', 'martin@email.sk', '+421 900 555 666',
 'Tablet', 'iPad Air 4', 'Problém s nabíjacím portom',
 'Port sa uvoľnil, nabíjací kábel drží len pri určitom uhle.',
 'iny', 0, 'osobne', 'hotovo', 60.00, 90.00);

INSERT INTO stav_historia (objednavka_id, stav, poznamka) VALUES
(1, 'caka',      'Objednávka prijatá'),
(1, 'diagnostika','Zariadenie prevzaté, začíname diagnostiku'),
(1, 'oprava',    'Objednali sme náhradný displej'),
(2, 'caka',      'Objednávka prijatá'),
(2, 'diagnostika','Overujeme stav batérie'),
(3, 'caka',      'Objednávka prijatá'),
(3, 'diagnostika','Diagnostika dokončená'),
(3, 'oprava',    'Port vymenený'),
(3, 'testovanie','Testujeme funkčnosť'),
(3, 'hotovo',    'Zariadenie je pripravené na vyzdvihnutie');

INSERT INTO spravy (objednavka_id, od_admina, sprava) VALUES
(1, 1, 'Dobrý deň, prevzali sme váš iPhone. Displej je naozaj vážne poškodený, no oprava je možná. Čakáme na náhradný diel, zvyčajne to trvá 1 deň.'),
(2, 1, 'Dobrý deň, batéria MacBooku je skutočne opotrebovaná. Kapacita je 41 % z pôvodnej. Odporúčame výmenu. Cena: 149 €. Potvrdíte?'),
(3, 1, 'Váš iPad je opravený a pripravený na vyzdvihnutie. Otváracie hodiny: Po–Pi 9:00–18:00.');

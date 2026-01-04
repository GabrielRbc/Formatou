-- ---------------------------------------------------------
-- Base de données : formatou
-- Projet : Site d'inscription à des formations
-- Auteur : Gabriel (projet scolaire)
-- Date : 2025-11-07
-- ---------------------------------------------------------

CREATE TABLE clients (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(20) NOT NULL,
    prenom VARCHAR(20) NOT NULL,
    date_naissance DATE NOT NULL,
    email VARCHAR(40) NOT NULL UNIQUE,
    motdepasse VARCHAR(255) NOT NULL,
    telephone VARCHAR(10),
    adresse VARCHAR(50),
    code_postal INT,
    ville VARCHAR(30),
    type_compte ENUM('client', 'employe', 'admin') DEFAULT 'client',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : categories_formations
-- ---------------------------------------------------------
CREATE TABLE categories_formations (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : formations
-- ---------------------------------------------------------
CREATE TABLE formations (
    id_formation INT AUTO_INCREMENT PRIMARY KEY,
    id_categorie INT NOT NULL,
    nom_formation VARCHAR(150) NOT NULL,
    description TEXT,
    duree VARCHAR(50),
    prix DECIMAL(6,2),
    image_url VARCHAR(255),
    FOREIGN KEY (id_categorie) REFERENCES categories_formations(id_categorie)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : disponibilites
-- ---------------------------------------------------------
CREATE TABLE disponibilites (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_formation INT NOT NULL,
    lieu VARCHAR(150) NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    nb_places_total INT NOT NULL,
    nb_places_dispo INT NOT NULL,
    FOREIGN KEY (id_formation) REFERENCES formations(id_formation)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : inscriptions
-- ---------------------------------------------------------
CREATE TABLE inscriptions (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_disponibilite INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('confirmée','en_attente','annulée') DEFAULT 'en_attente',
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_disponibilite) REFERENCES disponibilites(id_disponibilite)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : paiements (optionnelle)
-- ---------------------------------------------------------
CREATE TABLE paiements (
    id_paiement INT AUTO_INCREMENT PRIMARY KEY,
    id_inscription INT NOT NULL,
    montant DECIMAL(6,2) NOT NULL,
    mode VARCHAR(50),
    statut ENUM('en_attente','payé','remboursé') DEFAULT 'en_attente',
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_inscription) REFERENCES inscriptions(id_inscription)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cartes_bancaires (
    id_carte INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    numero_carte VARCHAR(16) NOT NULL,
    date_expiration VARCHAR(5) NOT NULL, -- format MM/AA
    cvc VARCHAR(3) NOT NULL,
    nom_carte VARCHAR(50) NOT NULL,
    date_enregistrement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : consentements_clients (RGPD)
-- ---------------------------------------------------------
CREATE TABLE consentements_clients (
    id_consentement INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    date_validation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : responsable_legaux (RGPD)
-- ---------------------------------------------------------

CREATE TABLE responsables_legaux (
    id_responsable INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(15),
    email VARCHAR(100),
    lien_parente VARCHAR(50), -- exemple : père, mère, tuteur
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TABLE : messages_contact
-- ---------------------------------------------------------

CREATE TABLE messages_contact (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    sujet VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('non_lu', 'en_cours', 'résolu') DEFAULT 'non_lu',
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reponses_contact (
    id_reponse INT AUTO_INCREMENT PRIMARY KEY,
    id_message INT NOT NULL,
    id_admin INT NOT NULL,
    reponse TEXT NOT NULL,
    date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_message) REFERENCES messages_contact(id_message)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_admin) REFERENCES clients (id_client)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- INSERTIONS DE DONNÉES D'EXEMPLE
-- ---------------------------------------------------------

-- Catégories
INSERT INTO categories_formations (nom_categorie, description) VALUES
('Secourisme', 'Formations de premiers secours'),
('Informatique', 'Apprentissage des outils numériques');

-- Formations
INSERT INTO formations (id_categorie, nom_formation, description, duree, prix, image_url) VALUES
(1, 'PSC - Premier Secours Citoyen', 'Lors de cette formation PSC, vous apprenez les gestes de premiers secours afin de savoir réagir efficacement en cas d’accident tels que l’arrêt cardiaque, la perte de connaissance, l’étouffement, les malaises ou encore des traumatismes.', '7 heures', 60.00, 'imgages/psc_image.png'),
(1, 'SST - Sauveteur Secouriste du Travail', "Le sauveteur secouriste intervient face à une situation d'accident du travail et porte les premiers secours à toute victime d'un accident ou d'un malaise. Il contribue à la prévention des risques professionnels en étant acteur de la prévention dans son entreprise.", '14 heures', 120.00, 'imgages/sst_image.png'),
(1, 'GQS - Gestes qui Sauvent', "Reconnue par l’État, la formation aux gestes qui sauvent permet à chacun de connaître les gestes à pratiquer lors d’accidents de la vie quotidienne ou de situations exceptionnelles. Après la formation, vous aurez acquis les compétences nécessaires pour porter secours à une personne en réalisant les premiers gestes de secours. A l’issue de la formation, chaque participant recevra une attestation reconnue par l’État.", '2 heures', 20.00, 'imgages/gqs_image.png'),
(1, 'IPSEN - Initiation aux Premiers Secours Enfant et Nourrisson', "Cette initiation sensibilise les participants à la prévention des accidents domestiques et de la vie courante et propose un apprentissage des gestes de premiers secours de l’enfant et du nourrisson.", '4,30 heures', 35.00, 'imgages/ipsen_image.png'),
(1, 'PSE1&2 - Premiers Secours en Équipe de niveau 1 et 2',"L’équipier secouriste agit en binôme, avec du matériel de premiers secours et en équipe constituée, sous la responsabilité d’un chef d'intervention. Il agit au sein d’un poste de secours ou d’une équipe de secours d’urgence.", '65 heures', 550.00, 'imgages/pse1&2_image.png'),
(1, 'PSE1 - Premiers Secours en Équipe de niveau 1', "La formation permet au secouriste d’améliorer ses futures missions, de mieux coordonner son action avec un ou plusieurs équipiers, d’apprendre à utiliser les nouveaux matériels et de prendre connaissance des nouvelles techniques et procédures", '6 heures', 80.00, 'imgages/pse1_image.png'),
(1, 'PSE2 - Premiers Secours en Équipe de niveau 2',"La formation permet au secouriste d’améliorer ses futures missions, de mieux coordonner son action avec un ou plusieurs équipiers, d’apprendre à utiliser les nouveaux matériels et de prendre connaissance des nouvelles techniques et procédures", '6 heures', 10.00, 'imgages/pse2_image.png'),
(1, 'BNSSA - Brevet National de Sécurité et de Sauvetage Aquatique',"Assurer la surveillance des lieux de baignades et assurer les premiers gestes de secours face à une victime se trouvant dans l’eau.", '60 heures', 400.00, 'imgages/bnssa_image.png'),
(1, 'IPS - Initiations aux premiers secours',"Cette formation vous permet de vous initier aux gestes de base de premiers secours, vous sensibiliser à la prise en charge de l’urgence cardiaque et de l’accident vasculaire cérébral et vous familiariser à l’utilisation des défibrillateurs.", '1 heure', 10.00, 'images/ips_image.png');

(2, 'Développement Web Full-Stack', 
'Cette formation complète forme les apprenants aux technologies essentielles du développement web moderne, du front-end au back-end. 
Vous apprendrez à créer des applications web dynamiques et performantes avec HTML5, CSS3, JavaScript ES6+, React pour l’interface utilisateur, 
et Node.js/Express pour la partie serveur. La formation aborde également la conception d’API REST, la gestion de bases de données (MongoDB, MySQL), 
les tests, le déploiement et les bonnes pratiques de sécurité et d’architecture. Idéale pour devenir développeur web polyvalent.', 
'40 heures', 1200.00, 'images/devWFS_image.png'),

(2, 'Administration Systèmes & Réseaux', 
'Cette formation enseigne la gestion et la maintenance des systèmes d’exploitation (principalement Linux) et des réseaux d’entreprise. 
Vous y apprendrez l’installation et la configuration des services réseau (DNS, DHCP, HTTP, SSH, etc.), 
la gestion des utilisateurs, des permissions, et la supervision du système. 
Des modules sont également dédiés à la configuration des routeurs et pare-feu, à la sécurité réseau et à la résolution d’incidents. 
Un parcours essentiel pour tout futur administrateur système ou technicien réseau.', 
'30 heures', 900.00, 'images/adS&R_image.png'),

(2, 'Cybersécurité - Fondamentaux', 
'Découvrez les bases de la cybersécurité à travers une approche concrète et accessible. 
Vous apprendrez à identifier les principales menaces, vulnérabilités et attaques (phishing, malware, ransomware, etc.), 
ainsi qu’à mettre en œuvre des mesures préventives. 
La formation inclut une initiation aux tests d’intrusion (pentesting), à la gestion des incidents de sécurité, 
à la protection des données et à la conformité réglementaire (RGPD). 
Des ateliers pratiques permettent d’expérimenter les outils essentiels du domaine.', 
'35 heures', 1500.00, 'images/cyber_image.png'),

(2, 'Data Science & Machine Learning', 
'Cette formation vous plonge dans le monde de la science des données et de l’intelligence artificielle. 
Vous y découvrirez comment collecter, nettoyer et analyser des jeux de données à l’aide de Python, Pandas, NumPy et Matplotlib. 
Les modules couvrent les modèles supervisés (régression, classification) et non supervisés (clustering, réduction de dimension), 
ainsi que la validation et le déploiement de modèles avec scikit-learn. 
Des études de cas réels vous aideront à comprendre les applications concrètes du Machine Learning dans divers secteurs.', 
'45 heures', 1800.00, 'images/dataScience_image.png'),

(2, 'DevOps & CI/CD', 
'Cette formation met l’accent sur l’automatisation et la collaboration entre les équipes de développement et d’exploitation. 
Vous apprendrez à mettre en place des pipelines d’intégration et de déploiement continus (CI/CD) avec GitLab, Jenkins ou GitHub Actions, 
à containeriser des applications avec Docker, et à orchestrer des environnements avec Kubernetes. 
La formation inclut également la surveillance et le logging, ainsi que les bonnes pratiques de gestion d’infrastructure as code (Terraform).', 
'32 heures', 1300.00, 'images/devOps&CICD_image.png'),

(2, 'Cloud (AWS / GCP) - Pratique', 
'Une formation orientée pratique sur les principaux services de cloud computing proposés par AWS et Google Cloud Platform. 
Vous apprendrez à déployer des applications, configurer des machines virtuelles, gérer le stockage et la sécurité, 
et surveiller les performances et coûts. 
L’accent est mis sur la compréhension des architectures cloud, l’automatisation, et les meilleures pratiques de sécurité. 
Des exercices guidés permettent d’acquérir une vraie autonomie sur ces plateformes.', 
'36 heures', 1600.00, 'images/cloud_image.png'),

(2, 'Mobile Development (iOS & Android)', 
'Cette formation couvre le développement d’applications mobiles natives et multiplateformes. 
Vous apprendrez à concevoir des interfaces intuitives et performantes, à gérer le cycle de vie des applications, 
et à publier vos projets sur l’App Store et le Play Store. 
Les technologies abordées incluent Swift, Kotlin, ainsi que React Native ou Flutter pour le cross-platform. 
Un focus particulier est donné à l’UX/UI mobile, aux tests et à l’optimisation des performances.', 
'30 heures', 1400.00, 'images/mobileDev_image.png'),

(2, 'Bases de données avancées (SQL & NoSQL)', 
'Approfondissez vos compétences en conception et gestion de bases de données relationnelles et non relationnelles. 
La formation aborde la modélisation avancée, l’optimisation des requêtes SQL, la gestion des transactions, 
ainsi que l’administration et la performance de systèmes tels que PostgreSQL et MongoDB. 
Vous apprendrez également les principes de la réplication, du sharding, et des sauvegardes/restaurations. 
Des études de cas vous permettront de comparer les approches SQL et NoSQL selon les besoins applicatifs.', 
'28 heures', 1100.00, 'images/bda_image.png');



-- Disponibilités (exemples)
INSERT INTO disponibilites (id_formation, lieu, date_debut, date_fin, nb_places_total, nb_places_dispo) VALUES
-- Formation 1
(1, 'Lyon - Salle Lumière', '2025-12-12 09:00:00', '2025-12-12 17:00:00', 15, 15),
(1, 'Paris - Centre République', '2025-12-18 09:00:00', '2025-12-18 16:30:00', 14, 14),
(2, 'Marseille - Espace Vieux-Port', '2026-01-08 09:00:00', '2026-01-09 16:00:00', 10, 10),

-- Formation 3
(3, 'Lyon - Salle Rhône', '2025-12-13 08:30:00', '2025-12-13 16:30:00', 12, 12),
(3, 'Paris - Tour Montparnasse', '2025-12-20 09:00:00', '2025-12-20 17:00:00', 16, 16),
(3, 'Marseille - Centre Joliette', '2026-01-07 09:00:00', '2026-01-07 16:00:00', 10, 10),

-- Formations 5 et 6
(5, 'Lyon - Cité Internationale', '2025-12-11 09:00:00', '2025-12-11 16:30:00', 20, 20),
(6, 'Paris - La Défense', '2025-12-17 09:30:00', '2025-12-17 16:30:00', 15, 15),
(6, 'Marseille - Prado', '2026-01-10 09:00:00', '2026-01-11 15:30:00', 12, 12),

-- Formations 7 et 8
(7, 'Lyon - Part-Dieu', '2025-12-14 09:00:00', '2025-12-14 16:00:00', 10, 10),
(7, 'Paris - Bercy Village', '2025-12-19 09:00:00', '2025-12-19 17:00:00', 12, 12),
(8, 'Marseille - Castellane', '2026-01-09 09:00:00', '2026-01-09 16:00:00', 10, 10),

-- Formation 9
(9, 'Lyon - Hôtel de Ville', '2025-12-16 09:00:00', '2025-12-16 16:00:00', 14, 14),
(9, 'Paris - Opéra Garnier', '2025-12-22 09:00:00', '2025-12-22 16:00:00', 16, 16),
(9, 'Marseille - La Canebière', '2026-01-12 09:00:00', '2026-01-12 16:00:00', 12, 12),

-- Formation 10 : Développement Web Full-Stack
(10, 'Lyon - La Cordée (Salle A)', '2025-12-02 09:00:00', '2025-12-05 17:00:00', 20, 20),
(10, 'Paris - Station F (Salle 3)', '2025-12-15 09:00:00', '2025-12-18 17:00:00', 18, 18),
(10, 'Nantes - Le Lieu Unique (Salle B)', '2026-01-12 09:00:00', '2026-01-15 17:00:00', 16, 16),

-- Formation 11 : Administration Systèmes & Réseaux
(11, 'Marseille - Espace Orange (Salle 2)', '2025-12-08 09:00:00', '2025-12-10 16:30:00', 14, 14),
(11, 'Lille - WAZEMMES Tech Hub', '2026-01-09 09:00:00', '2026-01-11 16:00:00', 12, 12),
(11, 'Rennes - CCI (Salle Informatique)', '2026-01-20 09:00:00', '2026-01-22 16:00:00', 12, 12),

-- Formation 12 : Cybersécurité - Fondamentaux
(12, 'Paris - La Défense (Salle Sécurité)', '2025-12-10 09:00:00', '2025-12-12 17:00:00', 16, 16),
(12, 'Bordeaux - CUBE (Salle 1)', '2026-01-06 09:30:00', '2026-01-08 17:00:00', 14, 14),
(12, 'Nice - Sophia Antipolis (Centre Tech)', '2026-01-26 09:00:00', '2026-01-28 16:00:00', 12, 12),

-- Formation 13 : Data Science & Machine Learning
(13, 'Paris - Station F (DataLab)', '2025-12-03 09:00:00', '2025-12-07 17:30:00', 22, 22),
(13, 'Toulouse - La Mêlée (Salle ML)', '2026-01-14 09:00:00', '2026-01-18 17:00:00', 18, 18),
(13, 'Grenoble - MINATEC (Salle 4)', '2026-02-02 09:00:00', '2026-02-06 17:00:00', 16, 16),

-- Formation 14 : DevOps & CI/CD
(14, 'Lille - Euratechnologies (Salle CI)', '2025-12-11 09:00:00', '2025-12-13 16:00:00', 15, 15),
(14, 'Paris - Bourse du Travail (Atelier DevOps)', '2025-12-20 09:30:00', '2025-12-22 16:30:00', 18, 18),
(14, 'Montpellier - Le Millénaire (Salle Tech)', '2026-01-19 09:00:00', '2026-01-21 16:00:00', 14, 14),

-- Formation 15 : Cloud (AWS / GCP) - Pratique
(15, 'Strasbourg - CCI (Salle Cloud)', '2025-12-09 09:00:00', '2025-12-11 17:00:00', 16, 16),
(15, 'Paris - La Défense (Cloud Lab)', '2025-12-23 09:00:00', '2025-12-25 16:00:00', 20, 20),
(15, 'Nantes - Atlanpole (Salle A)', '2026-01-13 09:00:00', '2026-01-15 16:00:00', 14, 14),

-- Formation 16 : Mobile Development (iOS & Android)
(16, 'Bordeaux - Darwin (Salle Mobile)', '2025-12-04 09:00:00', '2025-12-06 16:00:00', 12, 12),
(16, 'Nice - Centre Méditerranée (Salle 2)', '2026-01-07 09:30:00', '2026-01-09 16:30:00', 12, 12),
(16, 'Rouen - Le 106 (Salle Digitale)', '2026-01-28 09:00:00', '2026-01-30 16:00:00', 10, 10),

-- Formation 17 : Bases de données avancées (SQL & NoSQL)
(17, 'Toulouse - Purpan (Salle DB)', '2025-12-16 09:00:00', '2025-12-18 16:00:00', 14, 14),
(17, 'Paris - Opéra (Salle Data)', '2025-12-29 09:00:00', '2025-12-30 16:00:00', 18, 18),
(17, 'Lyon - Confluence (Salle 5)', '2026-01-16 09:00:00', '2026-01-17 16:00:00', 16, 16);
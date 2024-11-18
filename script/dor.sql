-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 13 mars 2024 à 12:59
-- Version du serveur : 8.0.31
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dor`
--

-- --------------------------------------------------------

--
-- Structure de la table `dor_or`
--

DROP TABLE IF EXISTS `dor_or`;
CREATE TABLE IF NOT EXISTS `dor_or` (
  `id` int NOT NULL,
  `ref` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_insert` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_update` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_vente` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_vendu` int DEFAULT NULL,
  `is_last` int DEFAULT NULL,
  `is_pre_order` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dor_or`
--

INSERT INTO `dor_or` (`id`, `ref`, `date_insert`, `date_update`, `date_vente`, `est_vendu`, `is_last`, `is_pre_order`) VALUES
(1, '123456789', '2024-03-01', NULL, NULL, 0, 1, 1),
(2, '123456788', '2024-02-29', NULL, NULL, 0, 1, 1),
(3, '123456787', '2024-02-29', NULL, NULL, 0, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `dor_pays`
--

DROP TABLE IF EXISTS `dor_pays`;
CREATE TABLE IF NOT EXISTS `dor_pays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_insert` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_modif` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dor_preorder`
--

DROP TABLE IF EXISTS `dor_preorder`;
CREATE TABLE IF NOT EXISTS `dor_preorder` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `cheque_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `or_stock_id` int DEFAULT NULL,
  `reference` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag_status` int NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_67AC49A9A76ED395` (`user_id`),
  UNIQUE KEY `UNIQ_67AC49A99F13B669` (`or_stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dor_role`
--

DROP TABLE IF EXISTS `dor_role`;
CREATE TABLE IF NOT EXISTS `dor_role` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dor_role`
--

INSERT INTO `dor_role` (`id`, `name`, `role`, `is_deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'ROLE_ADMIN', 0, '2024-02-29', NULL, NULL),
(2, 'user', 'ROLE_USER', 0, '2024-02-29', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `dor_setting`
--

DROP TABLE IF EXISTS `dor_setting`;
CREATE TABLE IF NOT EXISTS `dor_setting` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(4000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dor_setting`
--

INSERT INTO `dor_setting` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'NATIONALITY', '0', '2024-02-29', NULL),
(2, 'CAMPAIGN_END_DATE', '2023-12-31', '2024-02-29', NULL),
(3, 'STOCK_INITIAL', '3', NULL, NULL),
(4, 'STOCK_ACTUEL', '3', NULL, NULL),
(5, 'PRIX_UNITAIRE_OR', '10.600.000', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `dor_user`
--

DROP TABLE IF EXISTS `dor_user`;
CREATE TABLE IF NOT EXISTS `dor_user` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `country_id` int DEFAULT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plain_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civility` int DEFAULT NULL,
  `marital_status` int DEFAULT NULL,
  `cin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_exp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rib` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `affiliation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account` int DEFAULT NULL,
  `partner_cin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_cin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_cin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_cin` longtext COLLATE utf8mb4_unicode_ci,
  `file_passport` longtext COLLATE utf8mb4_unicode_ci,
  `file_partner_cin` longtext COLLATE utf8mb4_unicode_ci,
  `file_rib` longtext COLLATE utf8mb4_unicode_ci,
  `file_affiliation` longtext COLLATE utf8mb4_unicode_ci,
  `file_iban` longtext COLLATE utf8mb4_unicode_ci,
  `file_family_record_book` longtext COLLATE utf8mb4_unicode_ci,
  `file_residence` longtext COLLATE utf8mb4_unicode_ci,
  `is_deleted` tinyint(1) NOT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1BF103E9E7927C74` (`email`),
  KEY `IDX_1BF103E9D60322AC` (`role_id`),
  KEY `IDX_1BF103E9F92F3E70` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dor_user`
--

INSERT INTO `dor_user` (`id`, `role_id`, `country_id`, `email`, `roles`, `password`, `plain_password`, `name`, `firstname`, `civility`, `marital_status`, `cin`, `passport`, `passport_exp`, `rib`, `affiliation`, `iban`, `swift`, `birthday`, `address`, `city`, `phone`, `account`, `partner_cin`, `partner_name`, `partner_firstname`, `father_cin`, `father_name`, `father_firstname`, `mother_cin`, `mother_name`, `mother_firstname`, `file_cin`, `file_passport`, `file_partner_cin`, `file_rib`, `file_affiliation`, `file_iban`, `file_family_record_book`, `file_residence`, `is_deleted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'azerty@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$cwUy8d/4jmguMAdlKGjfzuApKGBnBccDz68WmCO02HbYq31nTB2Ge', 'secret', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(2, 2, NULL, 'felana@gmail.com', '[\"ROLE_USER\"]', '$2y$13$g/1ePzwK2SZILrb.2d85MOwdAxRDjLOUypDMudhouoFjQiwB0DFgy', 'C29.qT&$!d%r', 'Andria', 'felana', 1, 1, '123456789101', NULL, NULL, '13467910111213141516178', NULL, NULL, NULL, '2024-03-21', 'Antaninarenina', 'Antananarivo', '0385475838', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'C:\\wamp64\\www\\dor/public/uploads/orders/OR2024P00002/Annotation-2023-10-18-103129-65e70c503cf08.png', NULL, NULL, 'C:\\wamp64\\www\\dor/public/uploads/orders/OR2024P00002/Annotation-2023-10-18-102856-65e70c503da09.png', NULL, NULL, NULL, NULL, 0, '2024-03-05', NULL, NULL),
(3, 2, NULL, 'c@gmail.com', '[\"ROLE_USER\"]', '$2y$13$PLvqYldnz7WhpRhmCilrGemKwMV4WmWsgPegVMSy6C18i8eQu00We', ']?+y--7-Fn?U', 'qsdf', 'qsdf', 3, 2, '147852369543', NULL, NULL, NULL, NULL, 'test', 'test', '2024-03-27', 'wxcvb', 'wxcvbn,', '123456', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'C:\\wamp64\\www\\dor/public/uploads/orders/OR2024P00003/Annotation-2023-05-10-170758-65e7ffa373223.png', NULL, NULL, NULL, NULL, 'C:\\wamp64\\www\\dor/public/uploads/orders/OR2024P00003/Annotation-2023-10-18-103129-65e7ffa374333.png', NULL, NULL, 0, '2024-03-06', NULL, NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `dor_preorder`
--
ALTER TABLE `dor_preorder`
  ADD CONSTRAINT `FK_67AC49A99F13B669` FOREIGN KEY (`or_stock_id`) REFERENCES `dor_or` (`id`),
  ADD CONSTRAINT `FK_67AC49A9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `dor_user` (`id`);

--
-- Contraintes pour la table `dor_user`
--
ALTER TABLE `dor_user`
  ADD CONSTRAINT `FK_1BF103E9D60322AC` FOREIGN KEY (`role_id`) REFERENCES `dor_role` (`id`),
  ADD CONSTRAINT `FK_1BF103E9F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `dor_pays` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

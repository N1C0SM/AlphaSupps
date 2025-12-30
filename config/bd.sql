CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 100 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci


CREATE TABLE `collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT 'NULL',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 19 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci





CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `subscribed_at` date NOT NULL DEFAULT curdate(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 26 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci







CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `product` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10, 2) NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `has_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `gift` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `items_json` longtext DEFAULT NULL,
  `subscription_id` varchar(255) DEFAULT NULL,
  `fulfillment_status` varchar(50) DEFAULT 'pending',
  `tracking_code` varchar(100) DEFAULT NULL,
  `tracking_url` varchar(500) DEFAULT NULL,
  `source` varchar(50) DEFAULT 'web',
  PRIMARY KEY (`id`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_subscription` (`subscription_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 102 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci







CREATE TABLE `packs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `link` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`link`)),
  `price` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sold` int(11) DEFAULT 0,
  `label` varchar(255) DEFAULT NULL,
  `visibility` enum('public', 'private', 'shared_link') DEFAULT NULL,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '[]' CHECK (json_valid(`features`)),
  `rating` varchar(255) DEFAULT NULL,
  `profit` decimal(10, 2) DEFAULT NULL,
  `highlighted` tinyint(1) DEFAULT NULL,
  `price_numeric` decimal(10, 2) DEFAULT NULL,
  `visibility_v2` enum(
    'public',
    'private',
    'shared_link',
    'pending_public',
    'rejected_public'
  ) DEFAULT 'public',
  `margin_pack` decimal(5, 2) DEFAULT 0.00,
  `visibility_state` enum(
    'public',
    'private',
    'shared_link',
    'pending_public',
    'rejected_public'
  ) DEFAULT 'public',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 134 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci







CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(400) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notify_subscribers` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 73 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci









CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coment` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `idSuplement` int(11) DEFAULT NULL,
  `idPack` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reviews_idPack` (`idPack`),
  KEY `idx_reviews_user_id` (`user_id`),
  KEY `idx_reviews_user` (`user_id`),
  KEY `idx_reviews_pack` (`idPack`),
  KEY `idx_reviews_supp` (`idSuplement`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`idSuplement`) REFERENCES `supplements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`idPack`) REFERENCES `packs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE = InnoDB AUTO_INCREMENT = 17 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci









CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `accent_color` varchar(20) DEFAULT '#e0b94d',
  `logo_url` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stripe_mode` varchar(255) DEFAULT 'NULL',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci








CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_data` longtext NOT NULL COMMENT 'JSON con productos y configuración',
  `stripe_subscription_id` varchar(255) DEFAULT NULL COMMENT 'ID de suscripción en Stripe',
  `status` enum('pending', 'active', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
  `total` decimal(10, 2) DEFAULT 0.00 COMMENT 'Total mensual de la suscripción',
  `created_at` datetime NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `last_renewal` datetime DEFAULT NULL COMMENT 'Última renovación mensual',
  `next_billing` datetime DEFAULT NULL COMMENT 'Próxima fecha de cobro',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `stripe_subscription_id` (`stripe_subscription_id`),
  CONSTRAINT `fk_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci









CREATE TABLE `supplements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `idCollection` int(11) DEFAULT NULL,
  `idBrand` int(11) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `studies` longtext DEFAULT NULL CHECK (json_valid(`studies`)),
  `cost` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'NULL',
  `price` varchar(255) DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sold` int(11) DEFAULT 0,
  `idPack` int(11) DEFAULT NULL,
  `rating` varchar(255) DEFAULT 'NULL',
  `label` varchar(255) DEFAULT NULL,
  `real_price` decimal(10, 2) DEFAULT NULL,
  `profit` decimal(10, 2) DEFAULT NULL,
  `higlited` tinyint(1) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`features`)),
  `margin_product` decimal(5, 2) DEFAULT 0.00,
  `margin_subscription` decimal(5, 2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idCollection` (`idCollection`),
  KEY `idBrand` (`idBrand`),
  CONSTRAINT `idBrand` FOREIGN KEY (`idBrand`) REFERENCES `brands` (`id`),
  CONSTRAINT `idCollection` FOREIGN KEY (`idCollection`) REFERENCES `collections` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 63 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci








CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'NULL',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 41 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci

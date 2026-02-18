SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `publication_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `author_id` int(11) NOT NULL,
  `image_link` varchar(255) DEFAULT 'default-product.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `articles`

INSERT INTO `articles` (`id`, `name`, `description`, `price`, `publication_date`, `author_id`, `image_link`) VALUES
(1, 'Laptop HP Pavilion', 'Ordinateur portable performant 15.6 pouces, 8GB RAM, 256GB SSD', 699.99, '2026-02-16 22:33:34', 1, 'product_69939d5006f84.jpg'),
(2, 'Souris Gaming RGB', 'Souris gaming haute précision avec éclairage RGB personnalisable', 49.99, '2026-02-16 22:33:34', 1, 'product_69939ec9d180f.png'),
(3, 'Clavier Mécanique', 'Clavier mécanique avec switches Cherry MX Red', 129.99, '2026-02-16 22:33:34', 1, 'product_69939ef85a2d4.jpg'),
(4, 'Casque Audio Bluetooth', 'Casque sans fil avec réduction de bruit active', 199.99, '2026-02-16 22:33:34', 1, 'product_69939f3c02a12.jpg'),
(6, 'voiture de sport', 'une voiture de sport', 30.00, '2026-02-16 22:37:36', 2, 'product_69939c30b2ae2.jpg');
-- --------------------------------------------------------

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_zipcode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `invoices`

INSERT INTO `invoices` (`id`, `user_id`, `transaction_date`, `amount`, `billing_address`, `billing_city`, `billing_zipcode`) VALUES
(1, 1, '2026-02-16 22:51:46', 1250000.00, 'adresse', 'Lormont', '33310'),
(2, 2, '2026-02-16 22:54:05', 149.97, 'adresse', 'Bordeaux', '33300');

-- --------------------------------------------------------

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `article_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `invoice_items`

INSERT INTO `invoice_items` (`id`, `invoice_id`, `article_id`, `article_name`, `quantity`, `unit_price`) VALUES
(1, 1, 6, 'voiture de sport', 1, 1250000.00),
(2, 2, 2, 'Souris Gaming RGB', 3, 49.99);

-- --------------------------------------------------------

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `stock`

INSERT INTO `stock` (`id`, `article_id`, `quantity`) VALUES
(1, 1, 10),
(2, 2, 47),
(3, 3, 25),
(4, 4, 15),
(6, 6, 32);

-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `users`

INSERT INTO `users` (`id`, `username`, `email`, `password`, `balance`, `profile_picture`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@ecommerce.com', '$2a$12$RlX3ouzZ72BE2yJqZJgiLOTS.dDh3Epb7piW6K6bxrnx1pkzZrEsS', 760000.00, 'default.jpg', 'admin', '2026-02-16 22:33:34'),
(2, 'matheo', 'matheo@gmail.com', '$2y$10$Ry8hYcYn.BWWLNALyEjUe.YfpLi4YU4bRgDdXYgxviU3IUWv5rsuW', 200.03, 'default.jpg', 'user', '2026-02-16 22:35:17');


ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_id` (`article_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


-- Contraintes pour les tables déchargées

ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

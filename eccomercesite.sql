-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2024 at 05:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eccomercesite`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReorderUserIDs` ()   BEGIN
    -- Disable foreign key checks
    SET foreign_key_checks = 0;
    
    -- Create a temporary table to store the new order
    CREATE TEMPORARY TABLE temp_users
    SELECT * FROM users ORDER BY id;
    
    -- Drop the primary key from the original table
    ALTER TABLE users DROP PRIMARY KEY;
    
    -- Truncate the original table
    TRUNCATE TABLE users;
    
    -- Reinsert the data with new IDs
    SET @new_id = 0;
    INSERT INTO users
    SELECT (@new_id:=@new_id+1) AS id, 
           username, password, email, created_at, address, age, gender, 
           payment_info, is_admin, profile_picture, is_active
    FROM temp_users
    ORDER BY created_at;
    
    -- Recreate the primary key
    ALTER TABLE users ADD PRIMARY KEY (id);
    
    -- Reset the auto-increment
    ALTER TABLE users AUTO_INCREMENT = 1;
    
    -- Drop the temporary table
    DROP TEMPORARY TABLE temp_users;
    
    -- Re-enable foreign key checks
    SET foreign_key_checks = 1;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `about_content`
--

CREATE TABLE `about_content` (
  `id` int(11) NOT NULL,
  `section` varchar(255) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_content`
--

INSERT INTO `about_content` (`id`, `section`, `content`) VALUES
(1, 'Company Overview', 'Our company is dedicated to providing high-quality products...'),
(2, 'Founders and Team', 'Our team consists of passionate individuals...'),
(3, 'Story Behind the Brand', 'Our brand was born from a simple idea...'),
(4, 'Contact Information', 'Email:  urbanstore@gmail.com\r\nPhone: (123) 456-7890\r\nAddress: 123 Main St, City, Country');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`, `fullname`, `email`, `admin_id`, `profile_picture`) VALUES
(4, 'admin1', '$2y$10$oI2985.gqQRTdeduuzCgs.g3Fs48QlTsZa001FnGrHTLiOCp1rHHC', '2024-08-26 13:45:31', 'admin1', 'aaaaa@gmail.com', '244004', 'pfp-4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `carousel_slides`
--

CREATE TABLE `carousel_slides` (
  `id` int(11) NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `slide_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel_slides`
--

INSERT INTO `carousel_slides` (`id`, `image_file`, `title`, `description`, `button_text`, `button_link`, `slide_order`) VALUES
(16, '66cdfd7db2479.png', 'New collection 1', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit', 'View page 1', 'https://www.daraz.com.bd/#?', 1),
(17, '66cdfdfdc4297.png', 'Collection 2', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit', 'View page 2', 'https://www.daraz.com.bd/#?', 2);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(1, NULL, 17, 1, '2024-08-15 12:30:05'),
(2, NULL, 17, 1, '2024-08-15 12:30:10'),
(3, NULL, 17, 1, '2024-08-15 12:30:42'),
(23, NULL, 18, 1, '2024-08-17 18:22:51'),
(29, NULL, 18, 1, '2024-08-26 15:29:55'),
(32, NULL, 29, 1, '2024-08-27 19:10:31'),
(33, NULL, 29, 1, '2024-08-27 19:10:36'),
(36, 7, 31, 2, '2024-08-28 07:24:58'),
(37, 7, 30, 1, '2024-08-28 07:25:27'),
(38, 7, 32, 1, '2024-08-28 07:25:29'),
(46, NULL, 29, 1, '2024-08-28 15:15:36'),
(47, NULL, 29, 1, '2024-08-28 15:15:49'),
(51, 6, 46, 3, '2024-08-31 10:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq_content`
--

CREATE TABLE `faq_content` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq_content`
--

INSERT INTO `faq_content` (`id`, `question`, `answer`) VALUES
(1, 'What is your return policy?', 'We offer a 30-day return policy for all unused items.'),
(2, 'How long does shipping take?', 'Shipping typically takes 3-5 business days within the continental US.');

-- --------------------------------------------------------

--
-- Table structure for table `featured_sections`
--

CREATE TABLE `featured_sections` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_sections`
--

INSERT INTO `featured_sections` (`id`, `title`, `subtitle`, `image_url`, `link_url`, `display_order`) VALUES
(1, 'hello', 'tryrty', 'https://unsplash.com/photos/aerial-photo-of-foggy-mountains-1527pjeb6jg', 'https://unsplash.com/photos/aerial-photo-of-foggy-mountains-1527pjeb6jg', 1),
(2, 'esdfsdf', 'dsffsdf', '../uploads/66d209a791b2e.png', 'sdfsdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `title`, `image_path`, `created_at`) VALUES
(1, 'h', '../uploads/istockphoto-916092484-612x612.jpg', '2024-08-27 18:16:19'),
(2, 'h', '../uploads/istockphoto-916092484-612x612.jpg', '2024-08-27 18:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `offer_carousel`
--

CREATE TABLE `offer_carousel` (
  `id` int(11) NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offer_carousel`
--

INSERT INTO `offer_carousel` (`id`, `image_file`, `title`, `description`, `button_text`, `button_link`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Screenshot_20240827_102653.png', 'r', 'rr', 'rr', 'rr', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '2024-08-27 19:06:28', '2024-08-27 19:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `offer_slides`
--

CREATE TABLE `offer_slides` (
  `id` int(6) UNSIGNED NOT NULL,
  `image_file` varchar(255) DEFAULT NULL,
  `offer_end_time` datetime DEFAULT NULL,
  `slide_order` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offer_slides`
--

INSERT INTO `offer_slides` (`id`, `image_file`, `offer_end_time`, `slide_order`) VALUES
(7, 'Screenshot_20240828_090703.png', '2024-08-31 09:14:00', 1),
(8, 'Screenshot_20240828_090710.png', '2024-10-22 09:36:00', 2),
(9, 'Screenshot_20240828_090725.png', '0000-00-00 00:00:00', 3),
(10, 'Screenshot_20240828_090733.png', '0000-00-00 00:00:00', 4);

-- --------------------------------------------------------

--
-- Table structure for table `offer_timer`
--

CREATE TABLE `offer_timer` (
  `id` int(11) NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offer_timer`
--

INSERT INTO `offer_timer` (`id`, `end_time`) VALUES
(1, '2024-09-04 00:14:34');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Processing','Processed','Shipped','Delivered') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shipping_address` varchar(255) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `total_amount`, `status`, `created_at`, `updated_at`, `shipping_address`, `order_date`) VALUES
(7, 6, NULL, 500.00, 'Shipped', '2024-08-23 16:57:32', '2024-08-28 11:14:22', 'hello', '2024-08-20 16:49:54'),
(8, 6, NULL, 5500.00, 'Processing', '2024-08-26 16:18:18', '2024-08-28 11:08:51', 'b', '2024-08-28 16:49:54'),
(9, 7, NULL, 999.00, 'Processing', '2024-08-27 17:41:46', '2024-08-27 17:41:46', 'Dhaka', '2024-08-28 16:49:54'),
(10, 7, NULL, 1000.00, 'Processing', '2024-08-20 07:07:51', '2024-08-28 11:08:26', '', '2024-08-25 16:49:54'),
(11, 7, NULL, 1000.00, 'Processing', '2024-08-28 07:08:20', '2024-08-28 11:13:18', '', '2024-08-04 16:49:54'),
(12, 7, NULL, 450.00, 'Processing', '2024-08-28 07:08:42', '2024-08-28 07:08:42', '', '2024-08-28 16:49:54'),
(13, 6, NULL, 1000.00, 'Processing', '2024-08-28 07:57:01', '2024-08-28 07:57:01', '', '2024-08-28 16:49:54'),
(14, 6, NULL, 950.00, 'Processing', '2024-08-28 08:05:06', '2024-08-28 11:23:36', '', '2024-08-10 16:49:54'),
(15, 6, NULL, 1000.00, 'Processing', '2024-08-28 08:48:31', '2024-08-28 11:11:50', '', '2024-08-15 16:49:54'),
(16, 6, NULL, 1000.00, 'Processing', '2024-08-28 10:51:57', '2024-08-28 11:13:46', '', '2024-08-04 16:51:57'),
(17, 6, NULL, 1000.00, 'Shipped', '2024-08-28 10:56:15', '2024-08-29 13:21:05', '', '2024-08-01 16:56:15'),
(18, 6, NULL, 450.00, 'Delivered', '2024-08-28 10:56:45', '2024-08-29 13:16:13', '', '2024-08-27 16:56:45'),
(19, 9, NULL, 1000.00, 'Delivered', '2024-08-28 15:18:50', '2024-08-29 13:15:46', '', '2024-08-28 21:18:50'),
(20, 6, NULL, 1000.00, 'Delivered', '2024-08-31 07:25:10', '2024-08-31 07:25:34', '', '2024-08-31 13:25:10'),
(22, 6, NULL, 1000.00, 'Delivered', '2024-08-31 07:39:20', '2024-08-31 07:39:29', '', '2024-08-31 13:39:20');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 19, 2, 800.00),
(2, 1, 18, 1, 1000.00),
(3, 3, 18, 1, 1000.00),
(4, 4, 17, 1, 500.00),
(5, 4, 18, 2, 1000.00),
(6, 5, 18, 1, 1000.00),
(7, 6, 17, 1, 500.00),
(8, 13, 29, 1, 1000.00),
(9, 14, 30, 1, 950.00),
(10, 15, 29, 1, 1000.00),
(11, 16, 29, 1, 1000.00),
(12, 17, 29, 1, 1000.00),
(13, 18, 31, 1, 450.00),
(14, 19, 29, 1, 1000.00),
(15, 20, 29, 1, 1000.00),
(17, 22, 29, 1, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `page` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `rating`, `page`, `created_at`) VALUES
(30, 'Spring Autumn Casual Chiffon Shirt Women Office Lady Shirts Fashion Female Long Sleeve Loose Solid Blouse Tops S-4XL', 'Lorem ipsum odor amet, consectetuer adipiscing elit. Nascetur netus condimentum netus aliquam etiam, torquent sapien vulputate. Eros penatibus varius et natoque enim dui. Praesent posuere dictum elit porttitor tortor nisl mus pulvinar felis. Viverra dis sociosqu velit, sagittis ridiculus ultricies vehicula. Cras habitant curae justo gravida condimentum dolor quam porta.', 950.00, 5.00, 'women', '2024-08-27 16:34:28'),
(31, 'NEW Stylish Premium Quality Fashionable POLO Shirt For Men', 'Lorem ipsum odor amet, consectetuer adipiscing elit. Nascetur netus condimentum netus aliquam etiam, torquent sapien vulputate. Eros penatibus varius et natoque enim dui. Praesent posuere dictum elit porttitor tortor nisl mus pulvinar felis. Viverra dis sociosqu velit, sagittis ridiculus ultricies vehicula. Cras habitant curae justo gravida condimentum dolor quam porta.', 450.00, 5.00, 'men', '2024-08-27 16:35:25'),
(32, 'Summer Baby Girls Clothing Sets Cotton Cartoon Swan T-Shirt+Sequin Skirts 2Pcs Suit Children Fashion Princess Kids Dress Outfits', 'Product details of Summer Baby Girls Clothing Sets Cotton Cartoon Swan T-Shirt+Sequin Skirts 2Pcs Suit Children Fashion Princess Kids Dress Outfits\r\nSummer Baby Girls Clothing Sets Cotton Cartoon Swan T-Shirt+Sequin Skirts 2Pcs Suit Children Fashion Princess Kids Dress Outfits', 999.00, 4.00, 'kids', '2024-08-27 16:47:13'),
(33, 'Multi Color T Shirt For Men - Comfortable', 'Product details of Multi Color T Shirt For Men - Comfortable\r\nSize: M, L, XL, XXL\r\nM-Length 26,Chest 36\r\nL-Length 27,Chest 38\r\nXL-Length 28,Chest 40\r\nXXL-Length 29,Chest 42\r\nFabric:Microfiber Jersey\r\nQuality:High Quality\r\nPrint Quality:3D Subly print\r\nType:Round Neck\r\nColor: As given picture\r\nCollection for Men & Women & Girls\r\nfrindly key: #t shirt, #t shirt for man, #tshirt, #টি শার্ট, #গেঞ্জি ছেলেদের, #t-shirt, #ছেলেদের গেঞ্জি, #গেঞ্জি, #genji, #tshirt for men, #ganji, #টি শার্ট নিউ 2023, #টি শার্ট ছেলেদের, #boys t shirt, #গেঞ্জি ছেলেদের new, #t shart, #heavy metal t shirt, #ganji for men, #xxl t shirt for men, #tree shirt, #t sart, #jersey cricket, #গেনজি ছেলেদের new, #jersy football, #new t shirt 2023, #টিসাট, #গেঞ্জি ছেলেদের2023, #টি শার্ট নিউ 2023 brand, #ছেলেদের টি শার্ট, #gengi, #হাতা লম্বা গেনজি, #ছেলেদের গেঞ্জি2023, #te shirt, #ashes t shirt, #টি শাট, #black t shirt for men, #stylish t shirt for men, #ledies tops, #white t shirt for men, #celeder t shirt, #টিসাট ছেলেদের, #গেঞ্জি ফুল হাতা, #t shirt for man winter collection, #এক কালার গেঞ্জি, jarchi, #blake t shirt, #ফুলহাতা গেঞ্জি2023, #colar t shirt, #v neck t shirt, #ছেলেদের গেনজি, #high quality t shirt, #tshirt for men new 2023 brand, #te shart, #coton t shirt, #tee, #sleeveless t shirt for women, #TshirtForMan, #BTSTShirt, #Genji, #TshirtForMen, #TShirt, #TShirtForMan, #গেঞ্জি ছেলেদের, #t shirt for man, #টি শার্ট\r\nBudget Friendly Product', 200.00, 4.00, 'men', '2024-08-29 13:23:34'),
(34, 'Men\'s Full Sleeve Round Neck Casual T-Shirt with Black Suti - Comfortable and Stylish Wear', 'Product details of Men\'s Full Sleeve Round Neck Casual T-Shirt with Black Suti - Comfortable and Stylish Wear\r\nProduct type : T- shirt\r\nProduct Brand : SP COLOURS FASHION\r\nProduct Material : Pure Cotton\r\nFabric GSM: 165-170.\r\n100% Export Quality and 100% Quality control T-Shirt.\r\nT-Shirt Measurement: Asian and Regular Fit.\r\nQuality: Incentive original T-Shirt.\r\nStatus: Comfortable and Fashionable Exclusive T-shirt.\r\nQuantity of Product : 1, 2,3,4,5 ….\r\nProduct Colour :(Same As Picture)\r\nProduct Size : M, L, XL\r\nSize: M=Long-27, Chest-36\r\nL=Long-28, Chest-38\r\nXL=Long-29, Chest-40.\r\nProduct Type : Casual\r\nShop Name:\r\nApplicable Scene: Daily\r\nSleeve Length : Short Sleeve\r\nApplicable Season: WINTER, Spring and Summer\r\nProduction Country : Bangladesh\r\nArea of Use : Office, University, Party, Any Festival\r\nExtra Facility : 7 Days Return Policy\r\nMaterial: Cotton\r\nCollar: O-Neck\r\nSleeve Style: Regular sleeve\r\nHooded: No\r\nGender: MEN\r\nPattern Type: Print\r\nPlace Of Origin: Bangladesh\r\ntype1: printed t shirt men winter t-shirt .\r\ntype2: Mens t- shirts fashion .\r\ntype3: fashion print long sleeve t shirt men\r\nStyle: Fashion\r\nProduct Description :  Welcome to my “SP CLOURS FASHION ,,This exclusive men\'s t-shirt is made of high-quality microfiber material, ensuring maximum comfort and durability. The long sleeves provide extra coverage, making it perfect for cooler weather. The black color adds a touch of sophistication to any casual outfit. The chaina design adds a unique and stylish touch to the t-shirt. Perfect for any occasion, this t-shirt is a must-have for any fashion-forward man\'s wardrobe.All T-shirt in stock whole year! If  We are selling this product from “. We plan to offer low cost products to our customers. We choose good quality local fabrics ,and we choose those fabrics to make good quality domestic product. We select skilled craftsmen to make each of our products. So the cutting and finishing of our product is very perfect. We always provide products that match the image. Proper QC is done before each product is delivered. So that bad product are not delivered to the customers in anyway .', 299.00, 3.80, 'men', '2024-08-29 13:24:48'),
(35, 'Premium Quality Long Sleeve Micro Stitch Shirt', 'Product details of Premium Quality Long Sleeve Micro Stitch Shirt\r\n𝗣𝗿𝗲𝗺𝗶𝘂𝗺 Micro 𝗦𝘁𝗶𝘁𝗰𝗵 𝗙𝗮𝗯𝗿𝗶𝗰𝘀 𝗙𝗮𝗿𝗺𝗮𝗹 𝗦𝗵𝗶𝗿𝘁SpandexSolid PatternRegular FitVery comfortable & high qualityMachine Washable𝐒𝐢𝐳𝐞 𝐃𝐞𝐭𝐚𝐢𝐥𝐬: M, L, XL, M = Chest 40\", Length 28\"L = Chest 42\", Length 29\"XL = Chest 44\", Length 30\"XXL = Chest 46\", Length 31\"\r\n𝗣𝗿𝗲𝗺𝗶𝘂𝗺 Micro 𝗦𝘁𝗶𝘁𝗰𝗵 𝗙𝗮𝗯𝗿𝗶𝗰𝘀 𝗙𝗮𝗿𝗺𝗮𝗹 𝗦𝗵𝗶𝗿𝘁\r\n\r\n\r\nSpandex\r\nSolid Pattern\r\nRegular Fit\r\nVery comfortable & high quality\r\nMachine Washable\r\n𝐒𝐢𝐳𝐞 𝐃𝐞𝐭𝐚𝐢𝐥𝐬: M, L, XL, \r\n\r\nM = Chest 40\", Length 28\"\r\n\r\nL = Chest 42\", Length 29\"\r\n\r\nXL = Chest 44\", Length 30\"\r\n\r\nXXL = Chest 46\", Length 31\"\r\n\r\n', 450.00, 4.50, 'men', '2024-08-29 13:27:31'),
(36, 'Nike Sportswear', 'Our signature Max90 fit gives this soft cotton tee a casual and comfortable feel, while graphics add a classic touch of heritage style.\r\n\r\n\r\nShown: Sail/Total Orange\r\nStyle: HQ2432-133\r\nSize & Fit\r\nModel is wearing size M and is 6′2″/188cm\r\nLoose fit: roomy and relaxed', 200.00, 5.00, 'men', '2024-08-29 13:30:14'),
(37, 'Nike Primary Men\'s Dri-FIT UV Full-Zip Versatile Hoodie', 'Soft and sweat-wicking, the Primary Hoodie is made from premium knit fabric with eyelets for extra airflow. Its classic fit with underarm side panels brings the comfort to help you move confidently.\r\n\r\n\r\nShown: Black/Black\r\nStyle: FZ0967-010\r\nModel is wearing size M and is 5′11″/180cm\r\nBig & Tall model is wearing size 3XL and is 6\'2\"/188cm\r\nStandard fit: easy and traditional\r\nSize Guide', 500.00, 4.00, 'men', '2024-08-29 13:31:37'),
(38, 'Manchester City Jersey 2023/24 - short Sleeve Collar Thai Premium - Football Jersey', 'Product details of Manchester City Jersey 2023/24 - short Sleeve Collar Thai Premium - Football Jersey\r\nProduct Type: T-shirt\r\nPrint: 100% Quality\r\nSublamination Print and Logo Embroidery\r\nColor: 100% Guarantee\r\nProduct: Same as Design\r\nMain Material: Chinigura Fabric (Thai Fabric)\r\nGender: Men/Woman\r\nStylish and Comfortable\r\nSize: M, L, XL, XXL\r\nSize: M-Length 26,Chest 36\r\nSize: L-Length 27,Chest 38\r\nSize : XL-Length 28,Chest 40\r\nSize : XXL-Length 29,Chest 42\r\n. We provide fashionable trendy and good quality product. We always ensure the best quality product. Our products are very comfortable to use. Please choose a right size while placing an order.\r\n#Jersey2024\r\nProduct Type: T-shirt\r\nPrint: 100% Quality\r\nSublamination Print and Logo Embroidery\r\nColor: 100% Guarantee\r\nProduct: Same as Design\r\nMain Material: Chinigura Fabric (Thai Fabric)\r\nGender: Men/Woman\r\nStylish and Comfortable\r\nSize: M, L, XL, XXL\r\nSize: M-Length 26,Chest 36\r\nSize: L-Length 27,Chest 38\r\nSize : XL-Length 28,Chest 40\r\nSize : XXL-Length 29,Chest 42\r\n. We provide fashionable trendy and good quality product. We always ensure the best quality product. Our products are very comfortable to use. Please choose a right size while placing an order.\r\n#Jersey2024', 220.00, 3.00, 'men', '2024-08-29 13:33:03'),
(39, 'Complete Your Wardrobe with a Black Cotton Combo T-Shirt & Pant for Men - T-Shirt & Trouser Pant for Men - Very Comfortable and Fashionable', 'Product details of Complete Your Wardrobe with a Black Cotton Combo T-Shirt & Pant for Men - T-Shirt & Trouser Pant for Men - Very Comfortable and Fashionable\r\n•Product Type: Tshirt and pant\r\n•Gender: Men\r\n•Country of Origin: Bangladesh\r\n•Main Material: Cotton\r\nSizeGuid For Pant :\r\nM- komar 30 long 39\r\nL- komar 32, long 40\r\nXL- komar 34, long 41\r\nT-Shirt size\r\nM Chest - 38\", Lenght - 27\r\nL Chest - 40\"Lenght - 28\"\r\nXL Chest - 42\", Lenght â€“ 29\"\r\n#TshirtForMan\r\n#BTSTShirt\r\n#Genji\r\n#TshirtForMen\r\n• Product Type: Tshirt and pant\r\n• Gender: Men\r\n• Country of Origin: Bangladesh\r\n• Main Material: Cotton\r\nSizeGuid For Pant :\r\nM- komar 30 long 39\r\nL- komar 32, long 40\r\nXL- komar 34, long 41\r\nT-Shirt size\r\nM Chest - 38\", Lenght - 27\r\nL Chest - 40\"Lenght - 28\"\r\nXL Chest - 42\", Lenght â€“ 29\"\r\n#TshirtForMan\r\n#BTSTShirt\r\n#Genji\r\n#TshirtForMen', 600.00, 4.00, 'men', '2024-08-29 13:34:33'),
(40, 'Nike Challenger Running Fanny Pack (Large, 1L)', 'You don\'t need to make hard choices on what comes with you during your runs. A secure zipped main compartment features a divider to keep small items organized. The contoured and lightweight design helps add comfort while keeping distracting bounce to a minimum. Keep cool and dry from start to finish with a mesh backing that enhances breathability.\r\n\r\n\r\nShown: Green\r\nStyle: N1007142-313', 310.00, 4.80, 'men', '2024-08-29 13:42:20'),
(41, 'Nike Sportswear Tech Fleece Windrunner', 'This product is made with at least 50% sustainable materials, using a blend of both recycled polyester and organic cotton fibers. The blend is at least 10% recycled fibers or at least 10% organic cotton fibers.\r\n\r\n\r\nCan you believe it\'s already been 10 years of Tech Fleece? We’re celebrating the occasion with the timeless Windrunner design you know in a new color palette inspired by natural minerals. Our premium, smooth-on-both-sides fleece feels warmer and softer than ever, while keeping the same lightweight build you love. Complete your look with matching joggers or your favorite pair of leggings. The future of fleece starts here.\r\n\r\n\r\nShown: Black/Black\r\nStyle: FB8338-010', 200.00, 4.00, 'women', '2024-08-29 13:46:17'),
(42, 'Fashionable Tops Ladies Short Sleeve Casual Girls Print New T-Shirt For Women', 'Product details of Fashionable Tops Ladies Short Sleeve Casual Girls Print New T-Shirt For Women\r\nSize: M, L, XL, XXL\r\nM-Length 26,Chest 36\r\nL-Length 27,Chest 38\r\nXL-Length 28,Chest 40\r\nXXL-Length 29,Chest 42\r\nFabric:Microfiber Jersey\r\nQuality:High Quality\r\nPrint Quality:3D Subly print\r\nType:Round Neck\r\nColor: As given picture\r\nCollection for Women & Girls\r\nfrindly key: #t shirt, #t shirt for man, #tshirt, #টি শার্ট, # Women Tshirt, #Girls Tshirt #গেঞ্জি ছেলেদের, #t-shirt, #ছেলেদের গেঞ্জি, #গেঞ্জি, #genji, #tshirt for men, #ganji, #টি শার্ট নিউ 2023, #টি শার্ট ছেলেদের, #boys t shirt, #গেঞ্জি ছেলেদের new, #t shart, #heavy metal t shirt, #ganji for men, #xxl t shirt for men, #tree shirt, #t sart, #jersey cricket, #গেনজি ছেলেদের new, #jersy football, #new t shirt 2023, #টিসাট, #গেঞ্জি ছেলেদের2023, #টি শার্ট নিউ 2023 brand, #ছেলেদের টি শার্ট, #gengi, #হাতা লম্বা গেনজি, #ছেলেদের গেঞ্জি2023, #te shirt, #ashes t shirt, #টি শাট, #black t shirt for men, #stylish t shirt for men, #ledies tops, #white t shirt for men, #celeder t shirt, #টিসাট ছেলেদের, #গেঞ্জি ফুল হাতা, #t shirt for man winter collection, #এক কালার গেঞ্জি, jarchi, #blake t shirt, #ফুলহাতা গেঞ্জি2023, #colar t shirt, #v neck t shirt, #ছেলেদের গেনজি, #high quality t shirt, #tshirt for men new 2023 brand, #te shart, #coton t shirt, #tee, #sleeveless t shirt for women, #TshirtForMan, #BTSTShirt, #Genji, #TshirtForMen, #TShirt, #TShirtForMan, #গেঞ্জি ছেলেদের, #t shirt for man, #টি শার্ট, #teashirt, #tshat, #tshirts, #addidas, #tesat, #tishart, #teshirt, #dropshoulder, #torr, #tsirt, #printshirt, #trishart, #ganje, #poli, #sweatshirts, #shohure, #teeshirt, #tshat, #tishat, #teashirt, #full t shirt for men, #smug, #brazil, #deen, #tsat,\r\nBudget Friendly Product\r\n\r\nSize: M, L, XL, XXL\r\nM-Length 26,Chest 36\r\nL-Length 27,Chest 38\r\nXL-Length 28,Chest 40\r\nXXL-Length 29,Chest 42\r\nFabric:Microfiber Jersey\r\nQuality:High Quality\r\nPrint Quality:3D Subly print\r\nType:Round Neck\r\nColor: As given picture\r\nCollection for Women & Girls', 100.00, 2.00, 'women', '2024-08-29 13:50:38'),
(43, 'Nike Culture Of Basketball', 'Keep warm on and off the court in this lightweight fleece hoodie. Smooth on the outside and brushed soft on the inside, it\'s an easy way to add a little extra warmth to your day. A spacious fit gives you a baggy, casual feel while a special wash treatment mimics the natural fading of your favorite pair of jeans.\r\n\r\n\r\nShown: Denim Turquoise/Mystic Navy/Denim Turquoise\r\nStyle: FZ5266-464\r\nSize & Fit\r\nModel is wearing size S and is 4′9″/144cm\r\nOversized fit: exaggerated and spacious\r\nSize Guide', 500.00, 5.00, 'kids', '2024-08-29 13:51:47'),
(44, 'Summer Teen Girls Clothing Sets Children Fashion Letter Tops + Pants 2Pcs Outfits Kids Tracksuit 5 6 7 8 9 10 11 12 13 14 Years', 'Product details of Summer Teen Girls Clothing Sets Children Fashion Letter Tops + Pants 2Pcs Outfits Kids Tracksuit 5 6 7 8 9 10 11 12 13 14 Years\r\nSummer Teen Girls Clothing Sets Children Fashion Letter Tops + Pants 2Pcs Outfits Kids Tracksuit 5 6 7 8 9 10 11 12 13 14 Years\r\n\r\nit is more correct to compare with the height:\r\n(Unit：CM)\r\n120=5T：Tops length=36 bust=82 shoulder=39 sleeve=15 Pants length=76 waist=48 hips=80, fits for: 105-115cm height / 5 Years Old\r\n\r\n130=6T：Tops length=38 bust=86 shoulder=42 sleeve=16 Pants length=80 waist=51 hips=84, fits for: 115-125cm height / 6 Years Old\r\n\r\n140=7T-8T：Tops length=40 bust=90 shoulder=44 sleeve=17 Pants length=85 waist=54 hips=89, fits for: 125-135cm height / 7-8 Years Old\r\n\r\n150=9T-10T：Tops length=42 bust=94 shoulder=47 sleeve=18 Pants length=89 waist=57 hips=94, fits for: 135-145cm height / 9-10 Years Old\r\n\r\n160=11T-12T：Tops length=44 bust=98 shoulder=49 sleeve=19 Pants length=94 waist=60 hips=99, fits for: 145-155cm height / 11-12 Years Old\r\n\r\n170=13T-14T：Tops length=46 bust=102 shoulder=52 sleeve=20 Pants length=98 waist=63 hips=103, fits for: 155-160cm height / 13-14 Years Old\r\nNote:\r\n1.There is 2-3% difference according to manual measurement.(1 inch = 2.54 cm)\r\n2.All measurement in cm, please note 1cm=0.3937ch, 1 inch=2.540cm\r\n3.Please check the figures carefully and order the right size, don\'t completely depend on the \" age advice\". It\'s just for reference.', 299.00, 3.00, 'kids', '2024-08-29 13:53:10'),
(46, 'Nike Sportswear Metro Ground', 'How do you level up a basic? Make it built to last and infuse it with subtle style. A densely woven knit fabric helps this cardigan hold up to all your adventures and then some. A V-neck design gives you a classic look while a relaxed fit makes for easy layering. See? Anything but basic.\r\n\r\n\r\nShown: Khaki\r\nStyle: FV8046-247', 600.00, 5.00, 'featured', '2024-08-29 14:05:59'),
(47, 'New Stylish Trouser and T-Shirt SET - Comfortable and soft Febric', 'Product details of New Stylish Trouser and T-Shirt SET - Comfortable and soft Febric\r\n#TshirtForMan\r\n#BTSTShirt\r\n#Genji\r\n#TshirtForMen\r\n#TShirtForMan\r\n#tshirtforman\r\n#tshirt\r\n#t shirt\r\n#t shirt for man\r\n#গেঞ্জি ছেলেদের\r\n#টি শার্ট\r\n\r\nT-shirt Size Chart Size :M (CHEST 38 LENGTH 27)L (CHEST 40 LENGTH 28)XL (CHEST 42 LENGTH 29)XXL (CHEST 44 LENGTH 29.5)Trouser Size Chart Size :M (Komor (Width) 27-29 , LENGTH 37)L (Komor (Width) 30-31 LENGTH 38 )XL (Komor (Width) 32-33 LENGTH 39)XXL (Komor (Width) 33-36 LENGTH 40)\r\n\r\n* (GSM 160-165)', 530.00, 4.00, 'featured', '2024-08-31 13:53:56'),
(48, 'American Style Zippered Long Sleeved T-shirt', 'Product details of American Style Zippered Long Sleeved T-shirt\r\nAmerican Style Zippered Long Sleeved T-shirt\r\n\r\n  \r\n \r\n  \r\n\r\n   \r\n\r\n    \r\n• Zippered Design :The zippered design on the front of the shirt adds a touch of originality and convenience, making it easy to put on and take off.\r\n\r\n \r\n    \r\n\r\n\r\n\r\n    \r\n• High-Quality Material :Made from high-quality polyester, this T-shirt is durable and designed to last, ensuring you get the most out of your purchase.\r\n\r\n \r\n    \r\n\r\n\r\n\r\n    \r\n• Slight Stretch :With a slight stretch, this T-shirt provides a comfortable fit, allowing for freedom of movement and ensuring you feel good while you look good.\r\n\r\n \r\n    \r\n\r\n\r\n\r\n    \r\n• Versatile Letter Pattern :The versatile letter pattern on the shirt adds a stylish touch, making it a great choice for both casual and more formal occasions.\r\n\r\n \r\n    \r\n\r\n\r\n\r\n    \r\n• Suitable for Spring/Summer :This T-shirt is perfect for spring and summer wear, providing comfort and style in warmer temperatures.', 400.00, 5.00, 'women', '2024-08-31 13:59:57'),
(49, 'Sexy Vintage Crop Tshirts Long Sleeve Slim T Shirt 2024 Autumn New Korean Style Chic Sweet Y2K Aesthetic Streetwear Female Tee', 'Product details of Sexy Vintage Crop Tshirts Long Sleeve Slim T Shirt 2024 Autumn New Korean Style Chic Sweet Y2K Aesthetic Streetwear Female Tee\r\nSexy Vintage Crop Tshirts Long Sleeve Slim T Shirt 2024 Autumn New Korean Style Chic Sweet Y2K Aesthetic Streetwear Female Tee\r\nSexy Vintage Crop Tshirts Long Sleeve Slim T Shirt 2024 Autumn New Korean Style Chic Sweet Y2K Aesthetic Streetwear Female Tee\r\n\r\n\r\n\r\nProduct details\r\n\r\nSize : one size\r\n\r\nGender :Women\r\n\r\nMaterial :Blended\r\n\r\nWeight: 130g(about 5g error)\r\n\r\nPacking :Opp bag\r\n\r\nSeason :All season\r\n\r\nNotes\r\n\r\n1. Manual measuring. please allow 1-3cm error, thank you\r\n\r\n2. Due to the difference between dfferent monitors ,the picture may notreflect the actual color of the item.We guarantee the style is the same as shown in the picture.\r\n\r\n3. Light-colored clothes should not be washed with dark-colored clothing, as they are easy to dye.\r\n\r\n4.This is asian size,please choose larger 1-2 size than us/uk size.thanks!', 665.00, 4.50, 'women', '2024-08-31 14:01:13'),
(50, 'Women Y2K Long Sleeve Top Tee Sexy Patchwork T-shirts O-neck Long Sleeve Print Tunic Tops Shirt Streetwear T-Shirt', 'Product details of Women Y2K Long Sleeve Top Tee Sexy Patchwork T-shirts O-neck Long Sleeve Print Tunic Tops Shirt Streetwear T-Shirt\r\nWomen Y2K Long Sleeve Top Tee Sexy Patchwork T-shirts O-neck Long Sleeve Print Tunic Tops Shirt Streetwear T-Shirt\r\nFeatures：\r\n\r\n•【Material】:Y2k e-girls 90s long sleeve crop top shirt are made of polyester. Soft,smooth, skin-friendly,lightweight,breathable,comfortable to wear. Slim fit long sleeve tops for women.\r\n\r\n•【Design】:Long sleeve,korean fashion v neck blouse,brown floral print top shirt,slim fit blouse tops,wings hot rhinestone,square collar floral,tie dye,butterfly print,y2k fashion long sleeve crop top, tops women 2021\r\n\r\n•【Style】:Fashion casual and sexy style. Long sleeve blouse tee,teen girl y2k graphic printed top,y2k fashion long sleeve,y2k e-girls 90s long sleeve shirt,harajuku aesthetic shirt, patchwork shirt,e-girls vintage streetwear, cyber y2k clothes.\r\n\r\n•【Occasion】: Y2k E-girl 90s long sleeve top,omen y2k v neck long sleeves y2k fashion tops is perfect for daily wear, birthday, school,work, school,club, party, dating, shopping, street, beach, vacation, etc.\r\n\r\n•【Match】: This Y2K Vintage blouse shirt is easily matched with bohe pants, baggy jeans, gothic skirt, high heels, denim shorts and leggings.Vintage harajuku streetwear, tie dye crop top shirts,y2k slim fit long sleeve.\r\n\r\n\r\nProduct Specification:\r\n\r\nMaterial: polyester\r\n\r\nColor: white, light gray\r\n\r\nSize: S,M,L\r\n\r\nPackage Included:\r\n\r\n1*Long sleeve top\r\n\r\nAttention:\r\n\r\n1.Due to the difference of light and screen, the color of the product in the photo may be slightly different. \r\n\r\n2.Allowable measurement error is +/- 1-3 centimeters, pictures are for reference only.', 299.99, 3.00, 'women', '2024-08-31 14:07:11'),
(51, 'Adicolor SST Track Pants Kids', 'Details\r\nRegular fit\r\nDrawcord on elastic waist\r\n100% recycled polyester tricot\r\nSide pockets\r\nRibbed cuffs\r\nImported\r\nProduct color: Blue\r\nProduct code: IY4007', 530.00, 3.00, 'kids', '2024-09-01 14:27:06'),
(52, 'CHINO PANTS', 'Details\r\nImported\r\nProduct color: Black\r\nProduct code: IZ4649', 799.00, 5.00, 'kids', '2024-09-01 14:29:05'),
(53, 'Adicolor Neuclassics Track Pants', 'Details\r\nLoose fit\r\nDrawcord on elastic waist\r\n53% cotton, 47% polyester (recycled)\r\nSide pockets\r\nContains a minimum of 70% recycled and renewable content\r\nImported\r\nProduct color: Black\r\nProduct code: JH3772', 350.00, 4.60, 'women', '2024-09-01 14:31:01'),
(54, 'Adicolor SST Track Pants', 'Details\r\nSlim fit\r\nDrawcord on elastic waist\r\n50% cotton, 43% polyester (recycled), 7% elastane\r\nMid rise\r\nZip pockets\r\nContains a minimum of 70% recycled and renewable content\r\nImported\r\nProduct color: Better Scarlet\r\nProduct code: IK6603', 445.00, 4.30, 'women', '2024-09-01 14:32:47'),
(55, 'Adicolor Classics 3-Stripes Maxi Dress', 'Details\r\nSlim fit\r\nRibbed V-neck\r\n93% cotton 7% elastane\r\nRibbed cuffs\r\nSlit hem\r\nMade with Better Cotton\r\nImported\r\nProduct color: Black\r\nProduct code: IK0439\r\n\r\n', 399.00, 4.20, 'women', '2024-09-01 14:34:18'),
(56, 'Kid Girl Puff Tulle Sleeve Bow Blouse and Elastic Pleated Skirt Summer Outfit Toddler Infant Clothing Set Kids Wear Ootd', 'Product details of Kid Girl Puff Tulle Sleeve Bow Blouse and Elastic Pleated Skirt Summer Outfit Toddler Infant Clothing Set Kids Wear Ootd\r\nKid Girl Puff Tulle Sleeve Bow Blouse and Elastic Pleated Skirt Summer Outfit Toddler Infant Clothing Set Kids Wear Ootd\r\n', 339.00, 4.40, 'kids', '2024-09-01 14:36:07'),
(57, 'Junior Girls Baseball Suit Kid Spring and Autumn Fashion Splicing Letters Jacket Pleated Skirt 2 Pieces Student Set', 'Product details of Junior Girls Baseball Suit Kid Spring and Autumn Fashion Splicing Letters Jacket Pleated Skirt 2 Pieces Student Set\r\nJunior Girls Baseball Suit Kid Spring and Autumn Fashion Splicing Letters Jacket Pleated Skirt 2 Pieces Student Set\r\n110cm：Recommended height 100-110cm.\r\n\r\n120cm: Recommended height 110-120cm.\r\n\r\n130cm: recommended height 120-130cm.\r\n\r\n140cm: recommended height 130-140cm.\r\n\r\n150cm: recommended height 140-150cm.\r\n\r\n160cm: recommended height 150-160cm.\r\n\r\n170cm: recommended height 160-170cm.', 579.00, 4.70, 'kids', '2024-09-01 14:37:49'),
(59, 'Sun Protection Clothing Women\'s Jacket Spring and Summer New Thin Student Loose All-Matching Jacket Baseball Uniform ins Tide', 'Product details of Sun Protection Clothing Women\'s Jacket Spring and Summer New Thin Student Loose All-Matching Jacket Baseball Uniform ins Tide\r\nFabric/Material: Polyester/Polyester (Polyester Fiber)\r\nIngredient Content: 81%(Inclusive)-90%(Inclusive)\r\nStyle: Simple Commute/Simple\r\nDetails of Clothing Style: Solid Color\r\nClothing Style: Regular\r\nCombination Form: Single Piece\r\nLength/Sleeve Length: Common Style/Long Sleeve\r\nStyle: Sun Protection\r\nCollar Type: round Neck\r\nwith/without Velvet: No Velvet\r\nSleeve Type: Regular', 779.00, 3.90, 'kids', '2024-09-01 14:40:03'),
(60, 'Yfashion Women Double Fleece Hoodies, Fluffy Cute Rabbit Ears Hooded Long Sleevepullover Sweater, Sweet Lovely Plush Warm Coat Jacket color', 'Product details of Yfashion Women Double Fleece Hoodies, Fluffy Cute Rabbit Ears Hooded Long Sleevepullover Sweater, Sweet Lovely Plush Warm Coat Jacket color\r\n--Soft and waxy: skin-friendly and comfortable, absorbs sweat and wicks moisture.\r\n--Bunny hooded: fashionable for age reduction, cute and ldlike.\r\n--Loose version: no body shape, no restraint on he upper body.\r\n--Pure color version: simple and casual.\r\n--Washing instructions: It is recommended o wash dark-colored clothes separately from light-colored clothes o avoid staining..', 300.00, 3.00, 'kids', '2024-09-01 14:41:21'),
(61, 'Air Jordan 1 Mid', 'The Air Jordan 1 Mid brings full-court style and premium comfort to an iconic look. Its Air-Sole unit cushions play on the hardwood, while the padded collar gives you a supportive feel.\r\n\r\n\r\nShown: Black/White/Gym Red\r\nStyle: BQ6472-079', 899.00, 5.00, 'featured', '2024-09-01 14:43:52'),
(62, 'Nike Dri-FIT', 'The Nike Dri-FIT T-Shirt delivers a soft feel, sweat-wicking performance and a great range of motion to get you through your workout in total comfort.\r\n\r\n\r\nShown: Black/White\r\nStyle: AR6029-010', 559.00, 4.00, 'featured', '2024-09-01 14:45:30'),
(63, 'Nike Dri-FIT Combo', 'The Nike Dri-FIT T-Shirt delivers a soft feel, sweat-wicking performance and a great range of motion to get you through your workout in total comfort.\r\n\r\n\r\nShown: Black/White\r\nStyle: AR6029-010', 1120.00, 4.90, 'featured', '2024-09-01 14:47:44'),
(64, 'Nike Dunk Low Retro', 'You can always count on a classic. Iconic color blocking combines with premium materials and plush padding for game-changing comfort that lasts. The possibilities are endless—how will you wear your Dunks?\r\n\r\n\r\nShown: White/White/Black\r\nStyle: FQ8249-100', 239.00, 5.00, 'featured', '2024-09-01 14:48:48'),
(65, 'Nike Club Men\'s Short-Sleeve Oxford Button-Up Shirt', 'Made from smooth woven cotton in an easygoing button-down design, this summer-ready top from our Nike Club collection gives you versatility for days. The roomy fit and drawcord hem let you style it how you like it—button it up for business or let it hang open for casual, free-flowing style.\r\n\r\n\r\nShown: Black/White\r\nStyle: FN3902-010', 449.00, 4.70, 'featured', '2024-09-01 14:50:05'),
(66, 'Air Jordan 11 Retro Low \"Diffused Blue\"', 'It may not be 11:11, but your wish came true—a new AJ11 is here to claim the top spot in your rotation. The eternally popular silhouette gets remixed with a low collar and blue-and-black accents. Premium leather and classic Nike Air cushioning underfoot keep the old-school Jordan magic alive.\r\n\r\n\r\nShown: White/Diffused Blue/Football Grey/Midnight Navy\r\nStyle: FV5121-104', 500.00, 4.80, 'featured', '2024-09-01 14:51:11'),
(67, 'Air Jordan 4 Retro \"Oxidized Green\" Big Kids\' Shoes', 'Here\'s your AJ4 done up in classic colors. It\'s built to the original specs and constructed with full-grain leather and textiles. And all your favorite AJ4 elements are there too, like the floating eyestays and the mesh-inspired side panels and tongue.\r\n\r\n\r\nShown: White/White/Neutral Grey/Oxidized Green\r\nStyle: FQ8213-103', 560.00, 5.00, 'featured', '2024-09-01 14:51:43');

-- --------------------------------------------------------

--
-- Table structure for table `product_comments`
--

CREATE TABLE `product_comments` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `image_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `image_file`, `created_at`) VALUES
(15, 18, 4, 'a', NULL, '2024-08-11 15:01:12'),
(29, 18, 3, 'whats this', '66c09feae9f40.png', '2024-08-17 13:04:42'),
(30, 18, 5, 'oh noooo', NULL, '2024-08-17 13:06:46'),
(32, 29, 6, 'hellp', NULL, '2024-08-31 07:59:53');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_file` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_file`, `image_url`) VALUES
(16, 17, '66b77b539e33c.png', NULL),
(17, 18, '66b77b8d644ea.png', NULL),
(18, 19, '66b77bedec46c.png', NULL),
(25, 26, '66ca16edd59fa.png', NULL),
(27, 29, '66cdff39ac096.png', NULL),
(28, 30, '66ce0014a44a0.png', NULL),
(29, 31, '66ce004dc376a.png', NULL),
(30, 32, '66ce03118c8eb.png', NULL),
(31, 33, '66d076569ebc8.png', NULL),
(32, 34, '66d076a0e478c.png', NULL),
(33, 35, '66d077439075a.png', NULL),
(34, 36, '66d077e69a2f9.png', NULL),
(35, 37, '66d07839136b0.png', NULL),
(36, 38, '66d0788f9c4e7.png', NULL),
(37, 39, '66d078e926da6.png', NULL),
(38, 40, '66d07abc42245.png', NULL),
(39, 41, '66d07ba9a3e13.png', NULL),
(40, 42, '66d07caec2d12.png', NULL),
(41, 43, '66d07cf3132bf.png', NULL),
(42, 44, '66d07d4694293.png', NULL),
(43, 46, '66d0804791ddc.png', NULL),
(44, 47, '66d320740bbe9.png', NULL),
(45, 48, '66d321ddb8108.png', NULL),
(46, 49, '66d32229621c0.png', NULL),
(47, 50, '66d3238f6fab4.png', NULL),
(48, 51, '66d479ba3242f.png', NULL),
(49, 52, '66d47a3137e9f.png', NULL),
(50, 53, '66d47aa5a92a5.png', NULL),
(51, 54, '66d47b0fd84d8.png', NULL),
(52, 55, '66d47b6a22461.png', NULL),
(53, 56, '66d47bd762cca.png', NULL),
(54, 57, '66d47c3d0328e.png', NULL),
(55, 59, '66d47cc3b8d68.png', NULL),
(56, 60, '66d47d112d318.png', NULL),
(57, 61, '66d47da8af634.png', NULL),
(58, 62, '66d47e0a1c569.png', NULL),
(59, 63, '66d47e9066a73.png', NULL),
(60, 64, '66d47ed00645f.png', NULL),
(61, 65, '66d47f1d7291b.png', NULL),
(62, 66, '66d47f5f05067.png', NULL),
(63, 67, '66d47f7fa137f.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`id`, `user_id`, `product_id`, `rating`, `review`, `created_at`) VALUES
(1, 6, 29, 5.00, NULL, '2024-08-31 08:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'hello@gmail.com', '2024-08-31 10:00:25'),
(2, 'anan@gmail.com', '2024-08-31 13:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `image`) VALUES
(1, 'David', 'CEO', 'istockphoto-916092484-612x612.jpg'),
(2, 'John', 'CTO', 'chelsea-fern-r_-M00daj2Y-unsplash.jpg'),
(3, 'hello', 'member', 'jakob-owens-lkMJcGDZLVs-unsplash.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `payment_info` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `address`, `age`, `gender`, `payment_info`, `is_admin`, `profile_picture`, `is_active`) VALUES
(6, 'user1', '$2y$10$H0sHrVpfBgiT1PPLuY30M.pel1W/jbkOkyG38d5CehW5iFSsMho7C', 'anan@gmail.com', '2024-08-23 16:56:49', 'ab', 18, 'Female', 'f', 0, '../uploads/66d2dc058fc64.png', 1),
(7, 'user', '$2y$10$AbcDjjUtnqLywkoWXuBvQeWf5eM8BAiCglfz2N8Se/nb85tpbVkzK', 'user@gmail.com', '2024-08-27 16:48:19', 'dhaka', 18, 'Male', 'abcd', 0, '../uploads/66ce0368c519d.jpg', 1),
(8, 'user2', '$2y$10$ypkdEWV3J01PPorOjNwrv.56hapDMiOvwLuV1DO1rGCvVWSbfR8XS', 'aaa@gmail.com', '2024-08-27 17:54:44', 'dhaka', 18, 'Male', 'abcd', 0, NULL, 1),
(9, 'user123', '$2y$10$Ec5imssTdW7LVxDYUYV03emzQvWz70SJ3yTDPTB8oMQvSHx.eYguC', 'aysha@gmail.com', '2024-08-28 15:18:09', 'dhaka', 18, 'Male', '5', 0, '../uploads/66cf562f1d5b8.jpg', 1);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `users_after_delete` AFTER DELETE ON `users` FOR EACH ROW BEGIN
    -- Step 1: Resequence IDs
    SET @new_id = 0;
    UPDATE users SET id = (@new_id := @new_id + 1) ORDER BY id;

    -- No need to reset AUTO_INCREMENT here as it's not allowed in a trigger.
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_content`
--
ALTER TABLE `about_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `admin_id` (`admin_id`);

--
-- Indexes for table `carousel_slides`
--
ALTER TABLE `carousel_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `cart_ibfk_1` (`user_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq_content`
--
ALTER TABLE `faq_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `featured_sections`
--
ALTER TABLE `featured_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offer_carousel`
--
ALTER TABLE `offer_carousel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offer_slides`
--
ALTER TABLE `offer_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offer_timer`
--
ALTER TABLE `offer_timer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_content`
--
ALTER TABLE `about_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `carousel_slides`
--
ALTER TABLE `carousel_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq_content`
--
ALTER TABLE `faq_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `featured_sections`
--
ALTER TABLE `featured_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offer_carousel`
--
ALTER TABLE `offer_carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offer_slides`
--
ALTER TABLE `offer_slides`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `offer_timer`
--
ALTER TABLE `offer_timer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD CONSTRAINT `product_comments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD CONSTRAINT `product_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `product_ratings_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

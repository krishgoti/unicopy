-- chatbot_documents: tracks documents uploaded to the RAG chatbot
-- Run this in the 'unihelp' database

CREATE TABLE IF NOT EXISTS `chatbot_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_id` varchar(32) NOT NULL COMMENT 'MD5 hash of filename',
  `filename` varchar(255) NOT NULL,
  `chunk_count` int(11) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

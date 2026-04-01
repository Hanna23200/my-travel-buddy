ALTER TABLE `users` 
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `gender`,
ADD COLUMN `birthdate` DATE NULL AFTER `phone`,
ADD COLUMN `occupation` VARCHAR(100) NULL AFTER `birthdate`,
ADD COLUMN `hometown` VARCHAR(100) NULL AFTER `occupation`,
ADD COLUMN `current_location` VARCHAR(100) NULL AFTER `hometown`,
ADD COLUMN `bio` TEXT NULL AFTER `current_location`,
ADD COLUMN `profile_img` VARCHAR(255) NULL AFTER `bio`;
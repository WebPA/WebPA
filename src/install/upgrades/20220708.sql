ALTER TABLE `pa2_assessment` ADD COLUMN view_feedback TINYINT NOT NULL DEFAULT 0 AFTER allow_feedback;
ALTER TABLE `pa2_user_justification` ADD CONSTRAINT `unique_user_justification` UNIQUE KEY(`assessment_id`, `group_id`, `user_id`, `marked_user_id`);
ALTER TABLE `pa2_user_justification` DROP PRIMARY KEY;
ALTER TABLE `pa2_user_justification` ADD COLUMN `id` INT AUTO_INCREMENT PRIMARY KEY FIRST;

CREATE TABLE `pa2_moderated_user_justification` (
    user_justification_id INT UNSIGNED NOT NULL,
    moderated_comment TEXT NOT NULL,
    CONSTRAINT pk_user_justification_id PRIMARY KEY (user_justification_id)
);

CREATE TABLE `pa2_user_justification_publish_date` (
    assessment_id CHAR(36) NOT NULL,
    publish_date DATETIME NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES pa2_assessment(assessment_id)
);

CREATE TABLE `pa2_user_justification_report` (
    user_justification_report_id VARCHAR(255) NOT NULL,
    assessment_id CHAR(36) NOT NULL,
    group_id CHAR(36) NOT NULL,
    user_id INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (user_justification_report_id),
    FOREIGN KEY (assessment_id) REFERENCES pa2_assessment(assessment_id),
    FOREIGN KEY (group_id) REFERENCES pa2_user_group(group_id),
    FOREIGN KEY (user_id) REFERENCES pa2_user(user_id)
);
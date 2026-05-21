-- Bolão da Copa 2026 - Database Migration
-- Run this script first

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS bolao_copa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bolao_copa;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(255)        NOT NULL,
    email          VARCHAR(255)        NULL,
    phone          VARCHAR(30)         NULL,
    password_hash  VARCHAR(255)        NOT NULL,
    created_at     TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teams
CREATE TABLE IF NOT EXISTS teams (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    short_name      VARCHAR(10)     NOT NULL,
    flag_url        VARCHAR(500)    NOT NULL DEFAULT '',
    group_name      CHAR(1)         NULL,
    confederation   VARCHAR(50)     NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Matches
CREATE TABLE IF NOT EXISTS matches (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_team_id    INT UNSIGNED        NOT NULL,
    away_team_id    INT UNSIGNED        NOT NULL,
    match_date      DATETIME            NOT NULL,
    venue           VARCHAR(255)        NOT NULL DEFAULT '',
    stage           VARCHAR(50)         NOT NULL DEFAULT 'group',
    group_name      CHAR(1)             NULL,
    match_number    INT UNSIGNED        NOT NULL DEFAULT 0,
    home_score      TINYINT UNSIGNED    NULL,
    away_score      TINYINT UNSIGNED    NULL,
    status          ENUM('scheduled','live','finished') NOT NULL DEFAULT 'scheduled',
    CONSTRAINT fk_match_home FOREIGN KEY (home_team_id) REFERENCES teams(id),
    CONSTRAINT fk_match_away FOREIGN KEY (away_team_id) REFERENCES teams(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pools (Bolões)
CREATE TABLE IF NOT EXISTS pools (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(255)        NOT NULL,
    code             VARCHAR(20)         NOT NULL,
    creator_id       INT UNSIGNED        NOT NULL,
    quota_value      DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    estimated_prize  DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    bet_visibility   ENUM('before_start','during_match') NOT NULL DEFAULT 'before_start',
    status           ENUM('active','finished')           NOT NULL DEFAULT 'active',
    created_at       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pools_code (code),
    CONSTRAINT fk_pool_creator FOREIGN KEY (creator_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pool Scoring Rules
CREATE TABLE IF NOT EXISTS pool_rules (
    id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pool_id                  INT UNSIGNED    NOT NULL,
    exact_score_points       TINYINT UNSIGNED NOT NULL DEFAULT 10,
    one_team_score_points    TINYINT UNSIGNED NOT NULL DEFAULT 5,
    draw_points              TINYINT UNSIGNED NOT NULL DEFAULT 3,
    goal_difference_points   TINYINT UNSIGNED NOT NULL DEFAULT 2,
    UNIQUE KEY uq_rules_pool (pool_id),
    CONSTRAINT fk_rules_pool FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pool Members
CREATE TABLE IF NOT EXISTS pool_members (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pool_id         INT UNSIGNED    NOT NULL,
    user_id         INT UNSIGNED    NOT NULL,
    accepted_rules  TINYINT(1)      NOT NULL DEFAULT 0,
    joined_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_member (pool_id, user_id),
    CONSTRAINT fk_member_pool FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE,
    CONSTRAINT fk_member_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pool Invitations
CREATE TABLE IF NOT EXISTS pool_invitations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pool_id     INT UNSIGNED    NOT NULL,
    invited_by  INT UNSIGNED    NOT NULL,
    email       VARCHAR(255)    NULL,
    token       VARCHAR(36)     NOT NULL,
    status      ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
    sent_via    ENUM('email','link') NOT NULL DEFAULT 'link',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME        NULL,
    UNIQUE KEY uq_invitation_token (token),
    CONSTRAINT fk_invite_pool FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE,
    CONSTRAINT fk_invite_user FOREIGN KEY (invited_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bets
CREATE TABLE IF NOT EXISTS bets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pool_id         INT UNSIGNED        NOT NULL,
    user_id         INT UNSIGNED        NOT NULL,
    match_id        INT UNSIGNED        NOT NULL,
    home_score      TINYINT UNSIGNED    NOT NULL DEFAULT 0,
    away_score      TINYINT UNSIGNED    NOT NULL DEFAULT 0,
    points_earned   SMALLINT UNSIGNED   NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bet (pool_id, user_id, match_id),
    CONSTRAINT fk_bet_pool  FOREIGN KEY (pool_id)  REFERENCES pools(id)   ON DELETE CASCADE,
    CONSTRAINT fk_bet_user  FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_bet_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pool Match Betting Period Settings
CREATE TABLE IF NOT EXISTS pool_match_settings (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pool_id             INT UNSIGNED    NOT NULL,
    match_id            INT UNSIGNED    NOT NULL,
    betting_open_at     DATETIME        NULL,
    betting_close_at    DATETIME        NULL,
    UNIQUE KEY uq_pms (pool_id, match_id),
    CONSTRAINT fk_pms_pool  FOREIGN KEY (pool_id)  REFERENCES pools(id)   ON DELETE CASCADE,
    CONSTRAINT fk_pms_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

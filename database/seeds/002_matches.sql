-- Matches seed for FIFA World Cup 2026 (official FIFA schedule)
-- 72 group stage matches + 32 knockout stage placeholder matches
-- All match_date values stored in Brasília time (UTC-3)
-- Team IDs match 001_teams.sql insertion order (1=MEX … 48=PAN)
-- Re-runnable: ON DUPLICATE KEY UPDATE refreshes schedule fields;
--              status is preserved if already changed from 'scheduled'
--              (finished matches are not reset)

USE bolao_copa;

-- ============================================================================
-- Chunk 1 – matches 1-100
-- ============================================================================
INSERT INTO `matches` (id, home_team_id, away_team_id, match_date, venue, stage, group_name, match_number, status) VALUES
-- ---- GROUP STAGE – ROUND 1 (Jun 11-17) ------------------------------------
-- Match 1  | Jun 11 | Group A | México vs África do Sul
(1,   1,  2,  '2026-06-11 13:00:00', 'Estadio Azteca, Cidade do México',        'group', 'A',  1,  'scheduled'),
-- Match 2  | Jun 11 | Group A | Rep. da Coreia vs Rep. Tcheca
(2,   3,  4,  '2026-06-11 20:00:00', 'Estadio Akron, Guadalajara',              'group', 'A',  2,  'scheduled'),
-- Match 3  | Jun 12 | Group B | Canadá vs Bósnia e Herzegovina
(3,   5,  6,  '2026-06-12 16:00:00', 'BMO Field, Toronto',                      'group', 'B',  3,  'scheduled'),
-- Match 4  | Jun 12 | Group D | Estados Unidos vs Paraguai
(4,  13, 14,  '2026-06-12 22:00:00', 'SoFi Stadium, Los Angeles',               'group', 'D',  4,  'scheduled'),
-- Match 5  | Jun 13 | Group B | Catar vs Suíça
(5,   7,  8,  '2026-06-13 16:00:00', 'Levi''s Stadium, Santa Clara',            'group', 'B',  5,  'scheduled'),
-- Match 6  | Jun 13 | Group C | Brasil vs Marrocos
(6,   9, 10,  '2026-06-13 19:00:00', 'MetLife Stadium, Nova York/NJ',           'group', 'C',  6,  'scheduled'),
-- Match 7  | Jun 13 | Group C | Haiti vs Escócia
(7,  11, 12,  '2026-06-13 22:00:00', 'Gillette Stadium, Boston',                'group', 'C',  7,  'scheduled'),
-- Match 8  | Jun 13 | Group D | Austrália vs Turquia
(8,  15, 16,  '2026-06-14 01:00:00', 'BC Place, Vancouver',                     'group', 'D',  8,  'scheduled'),
-- Match 9  | Jun 14 | Group E | Alemanha vs Curaçau
(9,  17, 18,  '2026-06-14 14:00:00', 'NRG Stadium, Houston',                    'group', 'E',  9,  'scheduled'),
-- Match 10 | Jun 14 | Group F | Holanda vs Japão
(10, 21, 22,  '2026-06-14 17:00:00', 'AT&T Stadium, Dallas',                    'group', 'F', 10,  'scheduled'),
-- Match 11 | Jun 14 | Group E | Costa do Marfim vs Equador
(11, 19, 20,  '2026-06-14 20:00:00', 'Lincoln Financial Field, Filadélfia',     'group', 'E', 11,  'scheduled'),
-- Match 12 | Jun 14 | Group F | Suécia vs Tunísia
(12, 23, 24,  '2026-06-14 23:00:00', 'Estadio BBVA, Monterrey',                 'group', 'F', 12,  'scheduled'),
-- Match 13 | Jun 15 | Group H | Espanha vs Cabo Verde
(13, 29, 30,  '2026-06-15 13:00:00', 'Mercedes-Benz Stadium, Atlanta',          'group', 'H', 13,  'scheduled'),
-- Match 14 | Jun 15 | Group G | Bélgica vs Egito
(14, 25, 26,  '2026-06-15 16:00:00', 'Lumen Field, Seattle',                    'group', 'G', 14,  'scheduled'),
-- Match 15 | Jun 15 | Group H | Arábia Saudita vs Uruguai
(15, 31, 32,  '2026-06-15 19:00:00', 'Hard Rock Stadium, Miami',                'group', 'H', 15,  'scheduled'),
-- Match 16 | Jun 15 | Group G | Irã vs Nova Zelândia
(16, 27, 28,  '2026-06-15 22:00:00', 'SoFi Stadium, Los Angeles',               'group', 'G', 16,  'scheduled'),
-- Match 17 | Jun 16 | Group I | França vs Senegal
(17, 33, 34,  '2026-06-16 16:00:00', 'MetLife Stadium, Nova York/NJ',           'group', 'I', 17,  'scheduled'),
-- Match 18 | Jun 16 | Group I | Iraque vs Noruega
(18, 35, 36,  '2026-06-16 19:00:00', 'Gillette Stadium, Boston',                'group', 'I', 18,  'scheduled'),
-- Match 19 | Jun 16 | Group J | Argentina vs Argélia
(19, 39, 40,  '2026-06-16 22:00:00', 'Arrowhead Stadium, Kansas City',          'group', 'J', 19,  'scheduled'),
-- Match 20 | Jun 16 | Group J | Áustria vs Jordânia
(20, 37, 38,  '2026-06-17 01:00:00', 'Levi''s Stadium, Santa Clara',            'group', 'J', 20,  'scheduled'),
-- Match 21 | Jun 17 | Group K | Portugal vs Rep. Dem. do Congo
(21, 41, 42,  '2026-06-17 14:00:00', 'NRG Stadium, Houston',                    'group', 'K', 21,  'scheduled'),
-- Match 22 | Jun 17 | Group L | Inglaterra vs Croácia
(22, 45, 46,  '2026-06-17 17:00:00', 'AT&T Stadium, Dallas',                    'group', 'L', 22,  'scheduled'),
-- Match 23 | Jun 17 | Group L | Gana vs Panamá
(23, 47, 48,  '2026-06-17 20:00:00', 'BMO Field, Toronto',                      'group', 'L', 23,  'scheduled'),
-- Match 24 | Jun 17 | Group K | Uzbequistão vs Colômbia
(24, 43, 44,  '2026-06-17 23:00:00', 'Estadio Azteca, Cidade do México',        'group', 'K', 24,  'scheduled'),
-- ---- GROUP STAGE – ROUND 2 (Jun 18-23) ------------------------------------
-- Match 25 | Jun 18 | Group A | Rep. Tcheca vs África do Sul
(25,  4,  2,  '2026-06-18 13:00:00', 'Mercedes-Benz Stadium, Atlanta',          'group', 'A', 25,  'scheduled'),
-- Match 26 | Jun 18 | Group B | Suíça vs Bósnia e Herzegovina
(26,  8,  6,  '2026-06-18 16:00:00', 'SoFi Stadium, Los Angeles',               'group', 'B', 26,  'scheduled'),
-- Match 27 | Jun 18 | Group B | Canadá vs Catar
(27,  5,  7,  '2026-06-18 19:00:00', 'BC Place, Vancouver',                     'group', 'B', 27,  'scheduled'),
-- Match 28 | Jun 18 | Group A | México vs Rep. da Coreia
(28,  1,  3,  '2026-06-18 22:00:00', 'Estadio Akron, Guadalajara',              'group', 'A', 28,  'scheduled'),
-- Match 29 | Jun 19 | Group D | Estados Unidos vs Austrália
(29, 13, 15,  '2026-06-19 16:00:00', 'Lumen Field, Seattle',                    'group', 'D', 29,  'scheduled'),
-- Match 30 | Jun 19 | Group C | Escócia vs Marrocos
(30, 12, 10,  '2026-06-19 19:00:00', 'Gillette Stadium, Boston',                'group', 'C', 30,  'scheduled'),
-- Match 31 | Jun 19 | Group C | Brasil vs Haiti
(31,  9, 11,  '2026-06-19 21:30:00', 'Lincoln Financial Field, Filadélfia',     'group', 'C', 31,  'scheduled'),
-- Match 32 | Jun 19 | Group D | Turquia vs Paraguai
(32, 16, 14,  '2026-06-20 00:00:00', 'Levi''s Stadium, Santa Clara',            'group', 'D', 32,  'scheduled'),
-- Match 33 | Jun 20 | Group F | Holanda vs Suécia
(33, 21, 23,  '2026-06-20 14:00:00', 'NRG Stadium, Houston',                    'group', 'F', 33,  'scheduled'),
-- Match 34 | Jun 20 | Group E | Alemanha vs Costa do Marfim
(34, 17, 19,  '2026-06-20 17:00:00', 'BMO Field, Toronto',                      'group', 'E', 34,  'scheduled'),
-- Match 35 | Jun 20 | Group E | Equador vs Curaçau
(35, 20, 18,  '2026-06-20 21:00:00', 'Arrowhead Stadium, Kansas City',          'group', 'E', 35,  'scheduled'),
-- Match 36 | Jun 20 | Group F | Tunísia vs Japão
(36, 24, 22,  '2026-06-20 23:00:00', 'Estadio BBVA, Monterrey',                 'group', 'F', 36,  'scheduled'),
-- Match 37 | Jun 21 | Group H | Espanha vs Arábia Saudita
(37, 29, 31,  '2026-06-21 13:00:00', 'Mercedes-Benz Stadium, Atlanta',          'group', 'H', 37,  'scheduled'),
-- Match 38 | Jun 21 | Group G | Bélgica vs Irã
(38, 25, 27,  '2026-06-21 16:00:00', 'SoFi Stadium, Los Angeles',               'group', 'G', 38,  'scheduled'),
-- Match 39 | Jun 21 | Group H | Uruguai vs Cabo Verde
(39, 32, 30,  '2026-06-21 19:00:00', 'Hard Rock Stadium, Miami',                'group', 'H', 39,  'scheduled'),
-- Match 40 | Jun 21 | Group G | Nova Zelândia vs Egito
(40, 28, 26,  '2026-06-21 22:00:00', 'BC Place, Vancouver',                     'group', 'G', 40,  'scheduled'),
-- Match 41 | Jun 22 | Group J | Argentina vs Áustria
(41, 39, 37,  '2026-06-22 14:00:00', 'AT&T Stadium, Dallas',                    'group', 'J', 41,  'scheduled'),
-- Match 42 | Jun 22 | Group I | França vs Iraque
(42, 33, 35,  '2026-06-22 18:00:00', 'Lincoln Financial Field, Filadélfia',     'group', 'I', 42,  'scheduled'),
-- Match 43 | Jun 22 | Group I | Noruega vs Senegal
(43, 36, 34,  '2026-06-22 21:00:00', 'MetLife Stadium, Nova York/NJ',           'group', 'I', 43,  'scheduled'),
-- Match 44 | Jun 22 | Group J | Jordânia vs Argélia
(44, 38, 40,  '2026-06-23 00:00:00', 'Levi''s Stadium, Santa Clara',            'group', 'J', 44,  'scheduled'),
-- Match 45 | Jun 23 | Group K | Portugal vs Uzbequistão
(45, 41, 43,  '2026-06-23 14:00:00', 'NRG Stadium, Houston',                    'group', 'K', 45,  'scheduled'),
-- Match 46 | Jun 23 | Group L | Inglaterra vs Gana
(46, 45, 47,  '2026-06-23 17:00:00', 'Gillette Stadium, Boston',                'group', 'L', 46,  'scheduled'),
-- Match 47 | Jun 23 | Group L | Panamá vs Croácia
(47, 48, 46,  '2026-06-23 20:00:00', 'BMO Field, Toronto',                      'group', 'L', 47,  'scheduled'),
-- Match 48 | Jun 23 | Group K | Colômbia vs Rep. Dem. do Congo
(48, 44, 42,  '2026-06-23 23:00:00', 'Estadio Akron, Guadalajara',              'group', 'K', 48,  'scheduled'),
-- ---- GROUP STAGE – ROUND 3 (Jun 24-27) ------------------------------------
-- Match 49 | Jun 24 | Group B | Suíça vs Canadá
(49,  8,  5,  '2026-06-24 16:00:00', 'BC Place, Vancouver',                     'group', 'B', 49,  'scheduled'),
-- Match 50 | Jun 24 | Group B | Bósnia e Herzegovina vs Catar
(50,  6,  7,  '2026-06-24 16:00:00', 'Lumen Field, Seattle',                    'group', 'B', 50,  'scheduled'),
-- Match 51 | Jun 24 | Group C | Escócia vs Brasil
(51, 12,  9,  '2026-06-24 19:00:00', 'Hard Rock Stadium, Miami',                'group', 'C', 51,  'scheduled'),
-- Match 52 | Jun 24 | Group C | Marrocos vs Haiti
(52, 10, 11,  '2026-06-24 19:00:00', 'Mercedes-Benz Stadium, Atlanta',          'group', 'C', 52,  'scheduled'),
-- Match 53 | Jun 24 | Group A | Rep. Tcheca vs México
(53,  4,  1,  '2026-06-24 22:00:00', 'Estadio Azteca, Cidade do México',        'group', 'A', 53,  'scheduled'),
-- Match 54 | Jun 24 | Group A | África do Sul vs Rep. da Coreia
(54,  2,  3,  '2026-06-24 22:00:00', 'Estadio BBVA, Monterrey',                 'group', 'A', 54,  'scheduled'),
-- Match 55 | Jun 25 | Group E | Equador vs Alemanha
(55, 20, 17,  '2026-06-25 17:00:00', 'MetLife Stadium, Nova York/NJ',           'group', 'E', 55,  'scheduled'),
-- Match 56 | Jun 25 | Group E | Curaçau vs Costa do Marfim
(56, 18, 19,  '2026-06-25 17:00:00', 'Lincoln Financial Field, Filadélfia',     'group', 'E', 56,  'scheduled'),
-- Match 57 | Jun 25 | Group F | Japão vs Suécia
(57, 22, 23,  '2026-06-25 20:00:00', 'AT&T Stadium, Dallas',                    'group', 'F', 57,  'scheduled'),
-- Match 58 | Jun 25 | Group F | Tunísia vs Holanda
(58, 24, 21,  '2026-06-25 20:00:00', 'Arrowhead Stadium, Kansas City',          'group', 'F', 58,  'scheduled'),
-- Match 59 | Jun 25 | Group D | Turquia vs Estados Unidos
(59, 16, 13,  '2026-06-25 23:00:00', 'SoFi Stadium, Los Angeles',               'group', 'D', 59,  'scheduled'),
-- Match 60 | Jun 25 | Group D | Paraguai vs Austrália
(60, 14, 15,  '2026-06-25 23:00:00', 'Levi''s Stadium, Santa Clara',            'group', 'D', 60,  'scheduled'),
-- Match 61 | Jun 26 | Group I | Noruega vs França
(61, 36, 33,  '2026-06-26 16:00:00', 'Gillette Stadium, Boston',                'group', 'I', 61,  'scheduled'),
-- Match 62 | Jun 26 | Group I | Senegal vs Iraque
(62, 34, 35,  '2026-06-26 16:00:00', 'BMO Field, Toronto',                      'group', 'I', 62,  'scheduled'),
-- Match 63 | Jun 26 | Group H | Uruguai vs Espanha
(63, 32, 29,  '2026-06-26 21:00:00', 'Estadio Akron, Guadalajara',              'group', 'H', 63,  'scheduled'),
-- Match 64 | Jun 26 | Group H | Cabo Verde vs Arábia Saudita
(64, 30, 31,  '2026-06-26 21:00:00', 'NRG Stadium, Houston',                    'group', 'H', 64,  'scheduled'),
-- Match 65 | Jun 26 | Group G | Egito vs Irã
(65, 26, 27,  '2026-06-27 00:00:00', 'Lumen Field, Seattle',                    'group', 'G', 65,  'scheduled'),
-- Match 66 | Jun 26 | Group G | Nova Zelândia vs Bélgica
(66, 28, 25,  '2026-06-27 00:00:00', 'BC Place, Vancouver',                     'group', 'G', 66,  'scheduled'),
-- Match 67 | Jun 27 | Group L | Panamá vs Inglaterra
(67, 48, 45,  '2026-06-27 18:00:00', 'MetLife Stadium, Nova York/NJ',           'group', 'L', 67,  'scheduled'),
-- Match 68 | Jun 27 | Group L | Croácia vs Gana
(68, 46, 47,  '2026-06-27 18:00:00', 'Lincoln Financial Field, Filadélfia',     'group', 'L', 68,  'scheduled'),
-- Match 69 | Jun 27 | Group K | Colômbia vs Portugal
(69, 44, 41,  '2026-06-27 20:30:00', 'Hard Rock Stadium, Miami',                'group', 'K', 69,  'scheduled'),
-- Match 70 | Jun 27 | Group K | Rep. Dem. do Congo vs Uzbequistão
(70, 42, 43,  '2026-06-27 20:30:00', 'Mercedes-Benz Stadium, Atlanta',          'group', 'K', 70,  'scheduled'),
-- Match 71 | Jun 27 | Group J | Argélia vs Áustria
(71, 40, 37,  '2026-06-27 23:00:00', 'Arrowhead Stadium, Kansas City',          'group', 'J', 71,  'scheduled'),
-- Match 72 | Jun 27 | Group J | Jordânia vs Argentina
(72, 38, 39,  '2026-06-27 23:00:00', 'AT&T Stadium, Dallas',                    'group', 'J', 72,  'scheduled'),
-- ---- KNOCKOUT STAGE – Round of 32 (Jul 04-11) ----------------------------
(73,  1,  1,  '2026-07-04 18:00:00', 'AT&T Stadium, Dallas',                    'round_of_32', NULL, 73,  'scheduled'),
(74,  1,  1,  '2026-07-04 21:00:00', 'MetLife Stadium, Nova York/NJ',            'round_of_32', NULL, 74,  'scheduled'),
(75,  1,  1,  '2026-07-05 18:00:00', 'SoFi Stadium, Los Angeles',               'round_of_32', NULL, 75,  'scheduled'),
(76,  1,  1,  '2026-07-05 21:00:00', 'Estadio Azteca, Cidade do México',         'round_of_32', NULL, 76,  'scheduled'),
(77,  1,  1,  '2026-07-06 18:00:00', 'Levi''s Stadium, Santa Clara',            'round_of_32', NULL, 77,  'scheduled'),
(78,  1,  1,  '2026-07-06 21:00:00', 'Hard Rock Stadium, Miami',                'round_of_32', NULL, 78,  'scheduled'),
(79,  1,  1,  '2026-07-07 18:00:00', 'Arrowhead Stadium, Kansas City',          'round_of_32', NULL, 79,  'scheduled'),
(80,  1,  1,  '2026-07-07 21:00:00', 'BC Place, Vancouver',                     'round_of_32', NULL, 80,  'scheduled'),
(81,  1,  1,  '2026-07-08 18:00:00', 'Gillette Stadium, Boston',                'round_of_32', NULL, 81,  'scheduled'),
(82,  1,  1,  '2026-07-08 21:00:00', 'Lincoln Financial Field, Filadélfia',     'round_of_32', NULL, 82,  'scheduled'),
(83,  1,  1,  '2026-07-09 18:00:00', 'Soldier Field, Chicago',                  'round_of_32', NULL, 83,  'scheduled'),
(84,  1,  1,  '2026-07-09 21:00:00', 'Estadio Akron, Guadalajara',              'round_of_32', NULL, 84,  'scheduled'),
(85,  1,  1,  '2026-07-10 18:00:00', 'Lumen Field, Seattle',                    'round_of_32', NULL, 85,  'scheduled'),
(86,  1,  1,  '2026-07-10 21:00:00', 'BMO Field, Toronto',                      'round_of_32', NULL, 86,  'scheduled'),
(87,  1,  1,  '2026-07-11 18:00:00', 'Estadio BBVA, Monterrey',                 'round_of_32', NULL, 87,  'scheduled'),
(88,  1,  1,  '2026-07-11 21:00:00', 'MetLife Stadium, Nova York/NJ',            'round_of_32', NULL, 88,  'scheduled'),
-- ---- KNOCKOUT STAGE – Round of 16 (Jul 13-16) ----------------------------
(89,  1,  1,  '2026-07-13 18:00:00', 'Estadio Azteca, Cidade do México',        'round_of_16', NULL, 89,  'scheduled'),
(90,  1,  1,  '2026-07-13 21:00:00', 'MetLife Stadium, Nova York/NJ',           'round_of_16', NULL, 90,  'scheduled'),
(91,  1,  1,  '2026-07-14 18:00:00', 'AT&T Stadium, Dallas',                    'round_of_16', NULL, 91,  'scheduled'),
(92,  1,  1,  '2026-07-14 21:00:00', 'SoFi Stadium, Los Angeles',               'round_of_16', NULL, 92,  'scheduled'),
(93,  1,  1,  '2026-07-15 18:00:00', 'Levi''s Stadium, Santa Clara',            'round_of_16', NULL, 93,  'scheduled'),
(94,  1,  1,  '2026-07-15 21:00:00', 'Hard Rock Stadium, Miami',                'round_of_16', NULL, 94,  'scheduled'),
(95,  1,  1,  '2026-07-16 18:00:00', 'Arrowhead Stadium, Kansas City',          'round_of_16', NULL, 95,  'scheduled'),
(96,  1,  1,  '2026-07-16 21:00:00', 'BC Place, Vancouver',                     'round_of_16', NULL, 96,  'scheduled'),
-- ---- KNOCKOUT STAGE – Quarterfinals (Jul 17-19) --------------------------
(97,  1,  1,  '2026-07-17 21:00:00', 'MetLife Stadium, Nova York/NJ',           'quarter_final', NULL, 97,  'scheduled'),
(98,  1,  1,  '2026-07-18 18:00:00', 'AT&T Stadium, Dallas',                    'quarter_final', NULL, 98,  'scheduled'),
(99,  1,  1,  '2026-07-18 21:00:00', 'SoFi Stadium, Los Angeles',               'quarter_final', NULL, 99,  'scheduled'),
(100, 1,  1,  '2026-07-19 21:00:00', 'Hard Rock Stadium, Miami',                'quarter_final', NULL, 100, 'scheduled'),
-- ---- KNOCKOUT STAGE – Semifinals (Jul 22-23) -----------------------------
(101, 1, 1, '2026-07-22 21:00:00', 'MetLife Stadium, Nova York/NJ',            'semi_final',  NULL, 101, 'scheduled'),
(102, 1, 1, '2026-07-23 21:00:00', 'AT&T Stadium, Dallas',                     'semi_final',  NULL, 102, 'scheduled'),
-- ---- KNOCKOUT STAGE – Third-place play-off (Jul 25) ----------------------
(103, 1, 1, '2026-07-25 21:00:00', 'Hard Rock Stadium, Miami',                 'third_place', NULL, 103, 'scheduled'),
-- ---- KNOCKOUT STAGE – Final (Jul 26) -------------------------------------
(104, 1, 1, '2026-07-26 21:00:00', 'MetLife Stadium, Nova York/NJ',            'final',       NULL, 104, 'scheduled')
ON DUPLICATE KEY UPDATE
  home_team_id = VALUES(home_team_id),
  away_team_id = VALUES(away_team_id),
  match_date   = VALUES(match_date),
  venue        = VALUES(venue),
  stage        = VALUES(stage),
  group_name   = VALUES(group_name),
  match_number = VALUES(match_number),
  status       = IF(status = 'scheduled', VALUES(status), status);

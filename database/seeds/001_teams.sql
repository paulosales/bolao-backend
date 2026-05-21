-- Teams seed for FIFA World Cup 2026 (official FIFA schedule)
-- 48 teams in 12 groups (A-L)
-- Flag URLs from flagcdn.com (SVG)
-- Re-runnable: ON DUPLICATE KEY UPDATE refreshes all non-PK fields

USE bolao_copa;

INSERT INTO teams (id, name, short_name, flag_url, group_name, confederation) VALUES
-- Group A
(1,  'México',               'MEX', 'https://flagcdn.com/mx.svg',      'A', 'CONCACAF'),
(2,  'África do Sul',        'RSA', 'https://flagcdn.com/za.svg',      'A', 'CAF'),
(3,  'Coreia do Sul',        'KOR', 'https://flagcdn.com/kr.svg',      'A', 'AFC'),
(4,  'República Tcheca',     'CZE', 'https://flagcdn.com/cz.svg',      'A', 'UEFA'),
-- Group B
(5,  'Canadá',               'CAN', 'https://flagcdn.com/ca.svg',      'B', 'CONCACAF'),
(6,  'Bósnia e Herzegovina', 'BIH', 'https://flagcdn.com/ba.svg',      'B', 'UEFA'),
(7,  'Catar',                'QAT', 'https://flagcdn.com/qa.svg',      'B', 'AFC'),
(8,  'Suíça',                'SUI', 'https://flagcdn.com/ch.svg',      'B', 'UEFA'),
-- Group C
(9,  'Brasil',               'BRA', 'https://flagcdn.com/br.svg',      'C', 'CONMEBOL'),
(10, 'Marrocos',             'MAR', 'https://flagcdn.com/ma.svg',      'C', 'CAF'),
(11, 'Haiti',                'HAI', 'https://flagcdn.com/ht.svg',      'C', 'CONCACAF'),
(12, 'Escócia',              'SCO', 'https://flagcdn.com/gb-sct.svg',  'C', 'UEFA'),
-- Group D
(13, 'Estados Unidos',       'USA', 'https://flagcdn.com/us.svg',      'D', 'CONCACAF'),
(14, 'Paraguai',             'PAR', 'https://flagcdn.com/py.svg',      'D', 'CONMEBOL'),
(15, 'Austrália',            'AUS', 'https://flagcdn.com/au.svg',      'D', 'AFC'),
(16, 'Turquia',              'TUR', 'https://flagcdn.com/tr.svg',      'D', 'UEFA'),
-- Group E
(17, 'Alemanha',             'GER', 'https://flagcdn.com/de.svg',      'E', 'UEFA'),
(18, 'Curaçau',              'CUW', 'https://flagcdn.com/cw.svg',      'E', 'CONCACAF'),
(19, 'Costa do Marfim',      'CIV', 'https://flagcdn.com/ci.svg',      'E', 'CAF'),
(20, 'Equador',              'ECU', 'https://flagcdn.com/ec.svg',      'E', 'CONMEBOL'),
-- Group F
(21, 'Holanda',              'NED', 'https://flagcdn.com/nl.svg',      'F', 'UEFA'),
(22, 'Japão',                'JPN', 'https://flagcdn.com/jp.svg',      'F', 'AFC'),
(23, 'Suécia',               'SWE', 'https://flagcdn.com/se.svg',      'F', 'UEFA'),
(24, 'Tunísia',              'TUN', 'https://flagcdn.com/tn.svg',      'F', 'CAF'),
-- Group G
(25, 'Bélgica',              'BEL', 'https://flagcdn.com/be.svg',      'G', 'UEFA'),
(26, 'Egito',                'EGY', 'https://flagcdn.com/eg.svg',      'G', 'CAF'),
(27, 'Irã',                  'IRN', 'https://flagcdn.com/ir.svg',      'G', 'AFC'),
(28, 'Nova Zelândia',        'NZL', 'https://flagcdn.com/nz.svg',      'G', 'OFC'),
-- Group H
(29, 'Espanha',              'ESP', 'https://flagcdn.com/es.svg',      'H', 'UEFA'),
(30, 'Cabo Verde',           'CPV', 'https://flagcdn.com/cv.svg',      'H', 'CAF'),
(31, 'Arábia Saudita',       'KSA', 'https://flagcdn.com/sa.svg',      'H', 'AFC'),
(32, 'Uruguai',              'URU', 'https://flagcdn.com/uy.svg',      'H', 'CONMEBOL'),
-- Group I
(33, 'França',               'FRA', 'https://flagcdn.com/fr.svg',      'I', 'UEFA'),
(34, 'Senegal',              'SEN', 'https://flagcdn.com/sn.svg',      'I', 'CAF'),
(35, 'Iraque',               'IRQ', 'https://flagcdn.com/iq.svg',      'I', 'AFC'),
(36, 'Noruega',              'NOR', 'https://flagcdn.com/no.svg',      'I', 'UEFA'),
-- Group J
(37, 'Áustria',              'AUT', 'https://flagcdn.com/at.svg',      'J', 'UEFA'),
(38, 'Jordânia',             'JOR', 'https://flagcdn.com/jo.svg',      'J', 'AFC'),
(39, 'Argentina',            'ARG', 'https://flagcdn.com/ar.svg',      'J', 'CONMEBOL'),
(40, 'Argélia',              'ALG', 'https://flagcdn.com/dz.svg',      'J', 'CAF'),
-- Group K
(41, 'Portugal',             'POR', 'https://flagcdn.com/pt.svg',      'K', 'UEFA'),
(42, 'Rep. Dem. do Congo',   'COD', 'https://flagcdn.com/cd.svg',      'K', 'CAF'),
(43, 'Uzbequistão',          'UZB', 'https://flagcdn.com/uz.svg',      'K', 'AFC'),
(44, 'Colômbia',             'COL', 'https://flagcdn.com/co.svg',      'K', 'CONMEBOL'),
-- Group L
(45, 'Inglaterra',           'ENG', 'https://flagcdn.com/gb-eng.svg',  'L', 'UEFA'),
(46, 'Croácia',              'CRO', 'https://flagcdn.com/hr.svg',      'L', 'UEFA'),
(47, 'Gana',                 'GHA', 'https://flagcdn.com/gh.svg',      'L', 'CAF'),
(48, 'Panamá',               'PAN', 'https://flagcdn.com/pa.svg',      'L', 'CONCACAF')
ON DUPLICATE KEY UPDATE
  name          = VALUES(name),
  short_name    = VALUES(short_name),
  flag_url      = VALUES(flag_url),
  group_name    = VALUES(group_name),
  confederation = VALUES(confederation);
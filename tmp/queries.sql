DELETE FROM fc__admin_users__owners WHERE module_id=27 AND object_id>11900;
/*DELETE
	m, ml
FROM
	fc__core__multilang AS m
	LEFT JOIN fc__core__multilang_link AS ml
		ON ml.multilang_id=m.id
WHERE m.module_id=26 AND m.entity IN ('event', 'match', 'person', 'team');
DELETE
	m, ml
FROM
	fc__core__multilang AS m
	LEFT JOIN fc__core__multilang_link AS ml
		ON ml.multilang_id=m.id
WHERE m.module_id=22;*/
DELETE
	m, ml
FROM
	fc__core__multilang AS m
	LEFT JOIN fc__core__multilang_link AS ml
		ON ml.multilang_id=m.id
WHERE m.module_id=27 AND m.entity='object' AND ml.entity_id>11900;
/*TRUNCATE TABLE fc__fc__contracts;
TRUNCATE TABLE fc__fc__event;
TRUNCATE TABLE fc__fc__match;
TRUNCATE TABLE fc__fc__person;
TRUNCATE TABLE fc__fc__personstat;
TRUNCATE TABLE fc__fc__placement;
TRUNCATE TABLE fc__fc__teams;
TRUNCATE TABLE fc__fc__teamstat;*/
DELETE f, fl
FROM fc__files AS f
	JOIN fc__files__link AS fl ON fl.file_id=f.file_id
WHERE fl.module_id=27 AND fl.object_id>11900;
DELETE m, o
FROM fc__tags__modules AS m
	JOIN fc__tags__objects AS o ON o.link_id=m.link_id
WHERE
	m.module_id=27 AND o.object_id>11900;
DELETE FROM fc__news__category_objects WHERE object_id>11900;
DELETE FROM fc__news__objects WHERE object_id>11900;
/*TRUNCATE TABLE fc__news__category_objects;
TRUNCATE TABLE fc__news__objects;
/*TRUNCATE TABLE fc__tags__modules;
TRUNCATE TABLE fc__tags__objects;
TRUNCATE TABLE fc__tags__sources;*/


-- SQL VIEW qui prévoit la jointure entre user, tour, link_tour_item--
CREATE VIEW view_link_user_item AS 
SELECT t.id as tour_id, t.iteration as iteration, u.id as user_id, u.login as user, li.item_id as item_id, li.done as done
FROM `tour` t 
LEFT JOIN link_tour_item li ON li.tour_id = t.id
LEFT JOIN user u ON t.user_id = u.id

-- SQL VIEW qui prévoit la jointure entre user, field, link_tour_field --
CREATE VIEW view_link_user_field AS 
SELECT t.id as tour_id, t.iteration as iteration, u.id as user_id, u.login as user, lf.field_id as field_id, lf.done as done
FROM `tour` t 
LEFT JOIN link_tour_field lf ON lf.tour_id = t.id
LEFT JOIN user u ON t.user_id = u.id
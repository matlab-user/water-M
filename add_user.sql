use user_db;
SET @my_uid = '';
CALL add_user_3( @my_uid, 'belam_huj@163.com', 'belam_huj@163.com', 'belam', 1430399590 );
UPDATE user_db.user_table SET state='unknown' WHERE uid=@my_uid;
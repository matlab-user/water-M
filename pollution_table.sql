CREATE TABLE IF NOT EXISTS data_db.pollution_table (
	`code` VARCHAR(32) NOT NULL,
	`v_name` VARCHAR(60) DEFAULT '',
	`field` CHAR(32) DEFAULT '',
	`unit` VARCHAR(32) DEFAULT '',
	`type` CHAR(32) DEFAULT '',
	PRIMARY KEY ( `code` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* -----------------------------------------------------------------------------------------*/
USE data_db;

DELIMITER |
/*
	自动根据设备的 gid1 号 和待注册参数的 remark 值，判断此参数是否已经注册。
	如何未注册，自动注册，同时注册其单位、名称等。
	
	remark 为数据帧中对于的参数字段名称
*/
DROP PROCEDURE IF EXISTS add_dev_p;
CREATE PROCEDURE add_dev_p( IN gid1 VARCHAR(32) CHARACTER SET utf8, IN in_name VARBINARY(64), IN in_remark VARBINARY(200), IN in_unit VARBINARY(64) )
F1:BEGIN
	
	SET @new_did = '';
	SET @unit_id = '';
	
	SELECT d_id FROM dev_data_unit WHERE dev_id=gid1 AND remark=in_remark;
	IF FOUND_ROWS()<= 0 THEN 
	
		/*  注册新参数  */
		SELECT MAX(d_id) INTO @new_did FROM dev_data_unit WHERE dev_id=gid1;
		IF ISNULL(@new_did) THEN
			SET @new_did = 0;
		ELSE
			SET @new_did = @new_did + 1;
		END IF;
		
		/* 注册单位 */
		CALL user_db.add_unit( @unit_id, in_unit );
		
		INSERT INTO dev_data_unit (dev_id, d_id, v_name, remark, utid, d_t)  VALUES ( gid1, @new_did, in_name, in_remark, @unit_id, 0 );
		
	END IF;
	
END F1
|

DROP PROCEDURE IF EXISTS add_belam_dev;
CREATE PROCEDURE add_belam_dev( IN gid1 VARCHAR(32) CHARACTER SET utf8 )
F2:BEGIN
	SELECT guid1 FROM dev_db.dev_table WHERE guid1=gid1;
	IF FOUND_ROWS()>0 THEN
		LEAVE F2;
	END IF;
	
	INSERT INTO dev_db.dev_table ( guid1, name, model, maker, state, owner, t, timezone ) VALUES ( gid1, '水质监测仪', 'BEW', '四川碧朗科技', 'running', 'belam_huj@163.com', 'up', 8 );
END F2
|

DELIMITER ;

/*
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'B03', '噪声', 			'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'L10', '累积百分声级L10', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'L5', 	'累积百分声级L5', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'L50', '累积百分声级L50', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'L90', '累积百分声级L90', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'L95', '累积百分声级L95', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'Ld', 	'夜间等效声级Ld', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'Ldn', '昼夜等效声级Ldn', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'Leq', '30秒等效声级Leq', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'LMn', '最小的瞬时声级', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'LMx', '最大的瞬时声级', 	'噪声', 'dB', 'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'Ln', 	'昼间等效声级Ln', 	'噪声', 'dB', 'N3.1' );

INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S01', 	'O<sub>2</sub>含量', 	'废气', '%', 				'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S02', 	'烟气流量', 			'废气', 'm<sup>3</sup>/h', 	'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S03', 	'烟气温度', 			'废气', '&#8451;', 			'N5.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S04', 	'烟气动压', 			'废气', 'MPa', 				'N4.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S05', 	'烟气湿度', 			'废气', '%', 				'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S06', 	'制冷温度', 			'废气', '&#8451;', 			'N3.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S07', 	'烟道截面积', 			'废气', 'M<sup>2</sup>', 	'N4.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( 'S08', 	'烟气压力', 			'废气', 'MPa', 				'N4.2' );

INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '01', '烟尘', 		'废气', 'mg/m<sup>3</sup>', 'N5.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '02', '二氧化硫',	'废气', 'mg/m<sup>3</sup>', 'N5.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '03', '氮氧化物', 	'废气', 'mg/m<sup>3</sup>', 'N5.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '04', '一氧化碳', 	'废气', 'mg/m<sup>3</sup>', 'N2.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '05', '硫化氢', 	'废气', 'mg/m<sup>3</sup>', 'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '06', '氟化物', 	'废气', 'mg/m<sup>3</sup>', 'N2.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '07', '氰化物', 	'废气', 'mg/m<sup>3</sup>', 'N3.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '08', '氯化氢', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '09', '沥青烟', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '10', '氨', 		'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '11', '氯气', 		'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '12', '二硫化碳', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '13', '硫醇', 		'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '14', '硫酸雾', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );

INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '15', '铬酸雾', 	'废气', 'mg/m<sup>3</sup>', 'N2.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '16', '苯系物', 	'废气', 'mg/m<sup>3</sup>', 'N4.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '17', '甲苯', 		'废气', 'mg/m<sup>3</sup>', 'N4.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '18', '二甲苯', 	'废气', 'mg/m<sup>3</sup>', 'N4.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '19', '甲醛', 		'废气', 'mg/m<sup>3</sup>', 'N3.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '20', '苯丙芘', 	'废气', 'mg/m<sup>3</sup>', 'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '21', '苯胺类', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '22', '硝基甲苯', 	'废气', 'mg/m<sup>3</sup>', 'N3.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '23', '氯苯类', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '24', '光气', 		'废气', 'mg/m<sup>3</sup>', 'N3.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '25', '碳氢化合物', '废气', 'mg/m<sup>3</sup>', 'N5.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '26', '乙醛', 			'废气', 'mg/m<sup>3</sup>', 'N3.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '27', '酚类', 			'废气', 'mg/m<sup>3</sup>', 'N3.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '28', '甲醇', 			'废气', 'mg/m<sup>3</sup>', 'N5.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '29', '氯乙烯', 		'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '30', '二氧化碳', 		'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '31', '汞及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N4.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '32', '铅及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N2.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '33', '镉及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N3.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '34', '锡及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N4.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '35', '镍及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N3.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '36', '铍及其化合物', 	'废气', 'mg/m<sup>3</sup>', 'N4.4' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '37', '林格曼黑度', 	'废气', 'mg/m<sup>3</sup>', 'N1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '99', '其它污染物', 	'废气', 'mg/m<sup>3</sup>', 'N1' );

INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '001', 'pH值', '污水', ' ', 'N2.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '002', '色度', '污水', '色度单位', 'N5.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '003', '悬浮物', '污水', 						'mg/l', 'N5.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '010', '生化需氧量(BOD<sub>5</sub>)', 	'污水', 'mg/l', 'N5.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '011', '化学需氧量(COD<sub>cr</sub>)', '污水', 'mg/l', 'N6.1' );

INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '015', '总有机碳', 	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '020',  '总汞', 		'污水',  'mg/l',  'N2.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '021',  '烷基汞',  	'污水',  'mg/l',  'N2.1' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '022',  '总镉',  		'污水',  'mg/l',  'N2.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '023',  '总铬',  		'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '024',  '六价铬',  	'污水',  'mg/l',  'N2.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '025',  '三价铬',  '污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '026',  '总砷',  	'污水',  'mg/l',  'N2.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '027',  '总铅',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '028',  '总镍',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '029',  '总铜',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '030',  '总锌',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '031',  '总锰',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '032',  '总铁',  	'污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '033',  '总银',  	'污水',  'mg/l',  'N2.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '034',  '总铍',  	'污水',  'mg/l',  'N2.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '035',  '总硒',  	'污水',  'mg/l',  'N2.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '036',  '锡',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '037',  '硼',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '038',  '钼',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '039',  '钡',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '040',  '钴',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '041',  '铊',  	'污水',  'mg/l',  'N3.6' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '060',  '氨氮',  	'污水',  'mg/l',  'N2.3' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '061',  '有机氮',  '污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '065',  '总氮',  	'污水',	 'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '080',  '石油类',  '污水',  'mg/l',  'N3.2' );
INSERT INTO data_db.pollution_table ( code, v_name, field, unit, type ) VALUES ( '101',  '总磷',  	'污水',  'mg/l',  'N3.2' );
*/
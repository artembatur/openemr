Alter view tobacco_vw as
SELECT fm.date as 'Form Date', fm.encounter as 'Encounter', fm.form_name as 'Form Name', fm.form_id as 'FormID', fm.pid as 'PID', lbf.form_id as 'LBF Form ID', lbf.field_id, lbf.field_value, deleted  
FROM forms AS fm  
JOIN lbf_data as lbf on fm.form_id = lbf.form_id  
where fm.pid = '10801' 
AND deleted = 0 AND(( lbf.field_id like 'TobaccoCounselRefer%' OR lbf.field_id = 'PatientTobacco' OR lbf.field_id = 'PassiveSmoke' ))   
Order by fm.date DESC
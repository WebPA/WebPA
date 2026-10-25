-- WebPA 2.0.0.10
--
-- Administrator user definition
--
-- Create user (change values as required, default password is the encrypted form of 'admin')
--

INSERT INTO pa2_user
SET
  forename = 'WebPA', lastname = 'Administrator', email = '',
  username = 'admin', password = '21232f297a57a5a743894a0e4a801fc3',
  admin = 1, disabled = 0;

--
-- Create sample module (change values as required)
--

INSERT INTO pa2_module
SET
  module_code = 'sample', module_title = 'Sample module';

--
-- Link admin user to sample module
--

INSERT INTO pa2_user_module
SET
  user_id = 1, module_id = 1, user_type = 'T';

--
-- Create a sample Likert form for the sample module
--

INSERT INTO pa2_form SET
  form_id = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
  form_name = 'Sample Likert Form',
  form_type = 'likert',
  form_xml = '<?xml version="1.0" ?><form><formid>AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE</formid><formname>Sample Likert Form</formname><formtype>likert</formtype><question><questionid>Q1</questionid><text>Contribution</text></question></form>';

--
-- Link sample form to sample module
--

INSERT INTO pa2_form_module SET
  form_id = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
  module_id = 1;

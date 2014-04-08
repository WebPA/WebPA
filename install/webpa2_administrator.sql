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

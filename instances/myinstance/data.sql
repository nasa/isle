INSERT INTO `myinstance_locations` (`id`, `center`, `bldg`, `room`) VALUES
(1, 'CENTER', '111', '100');

INSERT INTO `myinstance_manufacturers` (`id`, `name`, `url`, `parent`) VALUES
(1, 'Dell', 'http://www.dell.com', NULL);

INSERT INTO `myinstance_asset_models` (`id`, `mfr`, `model`, `desc`, `series`, `url`, `img`) VALUES
(1, 1, '350', 'Dell Precision 350 Desktop Computer', 'Precision', NULL, NULL);

INSERT INTO `myinstance_assets` (`id`, `model`, `location`, `serial`, `notes`) VALUES
(1, 1, 1, 'G709641', NULL);

# IDs 5-11: SI base units (http://en.wikipedia.org/wiki/SI_base_unit)
# IDs 12-33: SI derived units (http://en.wikipedia.org/wiki/SI_derived_unit)
# IDs 34-40: Custom units
# Nonstandard SI units (http://en.wikipedia.org/wiki/Non-SI_units_accepted_for_use_with_SI)
# CGS units (http://en.wikipedia.org/wiki/Centimetre%E2%80%93gram%E2%80%93second_system_of_units)
INSERT INTO `myinstance_attribute_types` (`id`, `unit`, `abbr`, `parent`) VALUES
(5, 'meter', 'm', 3),
(6, 'kilogram', 'kg', 3),
(7, 'second', 's', 3),
(8, 'ampere', 'A', 3),
(9, 'kelvin', 'K', 3),
(10, 'candela', 'cd', 3),
(11, 'mole', 'mol', 3),
(12, 'hertz', 'Hz', 3),
(13, 'radian', 'rad', 3),
(14, 'steradian', 'sr', 3),
(15, 'newton', 'N', 3),
(16, 'pascal', 'Pa', 3),
(17, 'joule', 'J', 3),
(18, 'watt', 'W', 3),
(19, 'coulomb', 'C', 3),
(20, 'volt', 'V', 3),
(21, 'farad', 'F', 3),
(22, 'ohm', 'Ω', 3),
(23, 'siemens', 'S', 3),
(24, 'weber', 'Wb', 3),
(25, 'tesla', 'T', 3),
(26, 'henry', 'H', 3),
(27, 'degree Celsius', '°C', 3),
(28, 'lumen', 'lm', 3),
(29, 'lux', 'lx', 3),
(30, 'becquerel', 'Bq', 3),
(31, 'gray', 'Gy', 3),
(32, 'sievert', 'Sv', 3),
(33, 'katal', 'kat', 3),
(34, 'DC current', 'Adc', 8),
(35, 'RMS current', 'Arms', 8),
(36, 'DC voltage', 'Vdc', 20),
(37, 'RMS voltage', 'Vrms', 20),
(38, 'bit', 'b', 3),
(39, 'byte', 'B', 3),
(40, 'bits per second', 'bps', 3);

INSERT INTO `myinstance_attributes` (`id`, `name`, `type`) VALUES
(1, 'Width', 5),
(2, 'Length', 5),
(3, 'Height', 5),
(4, 'Mass', 6),
(5, 'Maximum Output Current', 34),
(6, 'Maximum Output Voltage', 36),
(7, 'Maximum Output Power', 18),
(8, 'Input Current', 8),
(9, 'Input Voltage', 37),
(10, 'Maximum Input Power', 18),
(11, 'Outputs', 2),
(12, 'Channels', 2),
(13, 'Minimum Frequency', 12),
(14, 'Maximum Frequency', 12),
(15, 'Minimum Temperature', 27),
(16, 'Maximum Temperature', 27),
(17, 'Operating System', 4),
(18, 'Processor', 4),
(19, 'Memory (RAM)', 39),
(20, 'Communication Interface', 4),
(21, 'Impedance', 22),
(22, 'Minimum Output Voltage', 36),
(23, 'Minimum Output Current', 34);

INSERT INTO `myinstance_categories` (`id`, `name`, `parent`) VALUES
(1, 'Computers', NULL),
(2, 'Laptop Computers', 1),
(3, 'Desktop Computers', 1),
(4, 'Displays', NULL),
(5, 'Monitors', 4),
(6, 'Televisions', 4);

INSERT INTO `myinstance_asset_model_categories` (`id`, `model`, `category`) VALUES
(1, 1, 3);

-- config-todo: add admin account
INSERT INTO `myinstance_users` (`id`, `uid`, `name`, `email`, `role`) VALUES
(1, 1, 'admin', 'admin@isle.local', 16);

DROP TABLE IF EXISTS contacts;
CREATE TABLE contacts 
(
`ID` varchar(3) NOT NULL,
`surname` varchar(80),
`firstname` varchar(80),
`phone` varchar(80),
`email` varchar(80),
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO contacts (ID, surname, firstname, phone, email) 
	VALUES ('MM', 'Mouse', 'Mickey', '555-1234', 'mickey@disney.com');
INSERT INTO contacts (ID, surname, firstname, phone, email) 
	VALUES ('DD', 'Duck', 'Donald', '555-1444', 'donald@disney.com');
INSERT INTO contacts (ID, surname, firstname, phone, email) 
	VALUES ('HRH', 'Highness', 'Her Royal', '604-555-9999', 'hrh@buckinghampalace.gov.uk');



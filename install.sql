DROP TABLE IF EXISTS wcf1_compulsory;
CREATE TABLE wcf1_compulsory (
	compulsoryID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	activationTime			INT(10) NOT NULL DEFAULT 0,
	addNewUser				TINYINT(1) DEFAULT 0,
	isDisabled				TINYINT(1) NOT NULL DEFAULT 0,
	isRefusable				TINYINT(1) NOT NULL DEFAULT 0,
	isMultilingual			TINYINT(1) NOT NULL DEFAULT 0,
	time					INT(10) NOT NULL DEFAULT 0,
	title					VARCHAR(80) NOT NULL DEFAULT '',
	userID					INT(10),
	username				VARCHAR(255) NOT NULL DEFAULT '',
	
	pages					TEXT NOT NULL,
	
	acceptUserAction		VARCHAR(10) NOT NULL DEFAULT '',
	acceptAddGroupIDs		TEXT,
	acceptRemoveGroupIDs	TEXT,
	acceptUrl				VARCHAR(255) NOT NULL DEFAULT '',
	
	hasPeriod				TINYINT(1) NOT NULL DEFAULT 0,
	periodEnd				INT(10) NOT NULL DEFAULT 0,
	periodStart				INT(10) NOT NULL DEFAULT 0,
	
	refuseUserAction		VARCHAR(10) NOT NULL DEFAULT '',
	refuseAddGroupIDs		TEXT,
	refuseRemoveGroupIDs	TEXT,
	refuseUrl				VARCHAR(255) NOT NULL DEFAULT '',
	
	statAccept				INT(10) DEFAULT 0,
	statRefuse				INT(10) DEFAULT 0,
	
	KEY (time),
	KEY (statAccept),
	KEY (statRefuse)
	
);

DROP TABLE IF EXISTS wcf1_compulsory_content;
CREATE TABLE wcf1_compulsory_content (
	contentID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	compulsoryID		INT(10) NOT NULL,
	languageID			INT(10),
	content				MEDIUMTEXT,
	subject				VARCHAR(255) DEFAULT '',
	hasEmbeddedObjects 	TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (compulsoryID, languageID)
);

DROP TABLE IF EXISTS wcf1_compulsory_dismissed;
CREATE TABLE wcf1_compulsory_dismissed (
	dismissedID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	compulsoryID		INT(10) NOT NULL,
	choice				VARCHAR(10),
	time				INT(10),
	userID				INT(10),
	username			VARCHAR(255) NOT NULL
);

-- foreign keys
ALTER TABLE wcf1_compulsory ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_compulsory_content ADD FOREIGN KEY (compulsoryID) REFERENCES wcf1_compulsory (compulsoryID) ON DELETE CASCADE;

ALTER TABLE wcf1_compulsory_dismissed ADD FOREIGN KEY (compulsoryID) REFERENCES wcf1_compulsory (compulsoryID) ON DELETE CASCADE;
ALTER TABLE wcf1_compulsory_dismissed ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

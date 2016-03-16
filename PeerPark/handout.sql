/*
 * Reference Schema for INFO2120 Assignment - 'PeerPark' Car Park Sharing Database
 * version 1.1
 *
 * PostgreSQL version...
 *
 * IMPORTANT!
 * You need to replace <postgres> with your PostgreSQL user name in line 267
 * of this file (the ALTER USER  command)
 */

/* clean-up to make script idempotent */
BEGIN TRANSACTION;
   DROP TABLE IF EXISTS public.BillingAccount CASCADE;
   DROP TABLE IF EXISTS public.Member CASCADE;
   DROP TABLE IF EXISTS public.BankAccount CASCADE;
   DROP TABLE IF EXISTS public.CreditCard CASCADE;
   DROP TABLE IF EXISTS public.PayPal CASCADE;
   DROP TABLE IF EXISTS public.MemberPhone CASCADE;
   DROP TABLE IF EXISTS public.Driver CASCADE;
   DROP TABLE IF EXISTS public.ParkTag CASCADE;
   DROP TABLE IF EXISTS public.Car CASCADE;
   DROP TABLE IF EXISTS public.CarType CASCADE;
   DROP TABLE IF EXISTS public.ParkPod CASCADE;
   DROP TABLE IF EXISTS public.ParkBay CASCADE;
   DROP TABLE IF EXISTS public.Booking CASCADE;
   DROP DOMAIN IF EXISTS public.RegoType;
   DROP DOMAIN IF EXISTS public.EMailType;
   DROP DOMAIN IF EXISTS public.AmountInCents;
   DROP SCHEMA IF EXISTS PeerPark CASCADE;
   DROP FUNCTION IF EXISTS BillingAccountNrFixer();
COMMIT;


/* let's go */
CREATE SCHEMA PeerPark;

/* this line will ensure that all following CREATE statements use the CarSharing schema */
/* it assumes that you have loaded our unidb schema from tutorial in week 6             */
SET search_Path = PeerPark, '$user', public, unidb;

/* for Member and PayPal table */
CREATE DOMAIN PeerPark.EMailType AS VARCHAR(50) CHECK (value SIMILAR TO '[[:alnum:]_]+@[[:alnum:]]+%.[[:alnum:]]+');

/* we will keep all monetary data as integer values representing cents */
CREATE DOMAIN AmountInCents AS INTEGER CHECK (VALUE >= 0);

/* for car registrations; */
/* Could check along lines of http://abitsmart.com/2010/02/validating-an-australian-drivers-license-number-using-regex/ */
CREATE DOMAIN PeerPark.RegoType AS CHAR(6);


CREATE TABLE MembershipPlan ( /* ADDED to be able to calculate costs of bookings */
   title         VARCHAR(20)   PRIMARY KEY,
   monthly_fee   AmountInCents NOT NULL, -- in cents
   hourly_rate   AmountInCents NOT NULL  -- in cents
);

CREATE TABLE PeerPark.Member (
  memberNo      INTEGER,
  email         EMailType    UNIQUE NOT NULL,
  nickName      VARCHAR(30)  UNIQUE,
  password      VARCHAR(64)  UNIQUE NOT NULL, /* ADDED: password; best stored as hash   */
  pw_salt       VARCHAR(25)  NOT NULL,        /* ADDED: salt value for hashed password */
  nameTitle     VARCHAR(10),
  nameGiven     VARCHAR(100),
  nameFamily    VARCHAR(100),
  adrStreetNo   INTEGER,
  adrStreet     VARCHAR(100),
  adrCity       VARCHAR(50),
  stat_since    DATE        DEFAULT CURRENT_DATE,  /* ADDED: member since ... */
  stat_nrOfBookings INTEGER DEFAULT 0,             /* ADDED: nr of bookings per member */
  stat_nrOfReviews  INTEGER DEFAULT 0,             /* ADDED: nr of reviews per member  */
  stat_sumPayments  AmountInCents DEFAULT 0,       /* ADDED: total member payments     */
  plan          VARCHAR(20) NOT NULL,              /* ADDED: plan on which a member is */
  prefBillingNo INTEGER     NOT NULL,  /* FK added later in script via ALTER TABLE */
  prefBay       INTEGER,               /* FK added later in script via ALTER TABLE */
  CONSTRAINT Member_PK PRIMARY KEY (memberNo),
  CONSTRAINT Member_Membership_FK FOREIGN KEY (plan) REFERENCES MembershipPlan(title),
  CONSTRAINT Title_CHK CHECK (nameTitle IN ('Mr','Mrs','Ms','Dr','Prof'))
);

BEGIN TRANSACTION;
INSERT INTO membershipplan (title, monthly_fee, hourly_rate) VALUES ('BigCar',4000,70);
INSERT INTO membershipplan (title, monthly_fee, hourly_rate) VALUES ('SmallCar',3000,50);
INSERT INTO member (email, nickname, password, nametitle, memberno, pw_salt, plan, prefBillingNo, prefBay, stat_nrOfBookings) VALUES ('abc@dog.com','abc','abcpass','Mr','11', 'peerpark','BigCar','111',561567,7);
INSERT INTO member (email, nickname, password, nametitle, memberno, pw_salt, plan, prefBillingNo, prefBay, stat_nrOfBookings) VALUES ('missk@dog.com','missk','misskpass','Mrs','12', 'peerpark','SmallCar','112',954673,12);
INSERT INTO member (email, nickname, password, nametitle, memberno, pw_salt, plan, prefBillingNo, prefBay, stat_nrOfBookings) VALUES ('user@dog.com','user','pass','Mr','13', 'peerpark','SmallCar','113',321567,1);
COMMIT;

CREATE TABLE PeerPark.MemberPhone (
  memberNo      INTEGER,
  phone         VARCHAR(20),
  CONSTRAINT MemberPhone_PK PRIMARY KEY (memberNo,phone),
  CONSTRAINT MemberPhone_Member_FK FOREIGN KEY (memberNo) REFERENCES Member(memberNo)  ON DELETE CASCADE
);

BEGIN TRANSACTION;
INSERT INTO MemberPhone (memberNo, phone) VALUES (11, 400123312);
INSERT INTO MemberPhone (memberNo, phone) VALUES (12, 700223366);
INSERT INTO MemberPhone (memberNo, phone) VALUES (13, 400444433);
COMMIT;

/* using ON UPDATE CASCADE allows to re-order BillingAccounts (updated billingNo) after one got deleted */
CREATE TABLE PeerPark.BillingAccount (
  memberNo      INTEGER,
  billingNo     INTEGER,
  CONSTRAINT BillingAccount_PK PRIMARY KEY (memberNo, billingNo),
  CONSTRAINT BillingAccount_Member_FK FOREIGN KEY (memberNo) REFERENCES Member(memberNo) ON DELETE CASCADE,
  CONSTRAINT BillingAccount_CHK CHECK (billingNo between 1 and 3)
);

BEGIN TRANSACTION;
INSERT INTO BillingAccount (memberNo, billingNo) VALUES (11, 1);
INSERT INTO BillingAccount (memberNo, billingNo) VALUES (12, 3);
INSERT INTO BillingAccount (memberNo, billingNo) VALUES (13, 2);
COMMIT;



/* ALTER TABLE PeerPark.Member
      ADD CONSTRAINT Member_BillingAccount_FK FOREIGN KEY (prefBillingNo, memberNo) REFERENCES BillingAccount(billingNo, memberNo) ON DELETE NO ACTION ON UPDATE CASCADE;
 */
CREATE TABLE PeerPark.BankAccount (
  memberNo      INTEGER,
  billingNo     INTEGER,
  name          VARCHAR(30) NOT NULL,
  accountNo     INTEGER     NOT NULL,
  bsb           CHAR(6)     NOT NULL,
  CONSTRAINT BankAccount_PK PRIMARY KEY (memberNo, billingNo),
  CONSTRAINT BankAccount_BillingAccount_FK FOREIGN KEY (memberNo,billingNo) REFERENCES BillingAccount(memberNo,billingNo)  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT BankAccount_BSB_CHK CHECK (bsb SIMILAR TO '[[:digit:]]{6}')
);

BEGIN TRANSACTION;
INSERT INTO BankAccount (memberNo, billingNo, name, accountNo, bsb) VALUES (11, 1, 'JackNilson', 125784, 665743);
INSERT INTO BankAccount (memberNo, billingNo, name, accountNo, bsb) VALUES (12, 3, 'DavideLancer', 481912, 665743);
INSERT INTO BankAccount (memberNo, billingNo, name, accountNo, bsb) VALUES (13, 2, 'LucyPall', 722278, 665743);
COMMIT;

CREATE TABLE PeerPark.CreditCard (
  memberNo      INTEGER,
  billingNo     INTEGER,
  name          VARCHAR(40) NOT NULL,
  brand         VARCHAR(10) NOT NULL,
  ccNo          CHAR(16)    NOT NULL,
  expires       CHAR(5)     NOT NULL,
  CONSTRAINT CreditCard_PK PRIMARY KEY (memberNo, billingNo),
  CONSTRAINT CreditCard_BillingAccount_FK FOREIGN KEY (memberNo,billingNo) REFERENCES BillingAccount(memberNo,billingNo)  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT CreditCard_Brand_CHK   CHECK (brand IN ('visa','master','amex')),
  CONSTRAINT CreditCard_ccNo_CHK    CHECK (ccNo SIMILAR TO '[[:digit:]]{16}'),
  CONSTRAINT CreditCard_Expires_CHK CHECK (expires SIMILAR TO '[[:digit:]][[:digit:]]/[[:digit:]][[:digit:]]')
);

BEGIN TRANSACTION;
INSERT INTO CreditCard (memberNo, billingNo, name, brand, ccNo, expires) VALUES (11, 1, 'JackNilson', 'visa',1223424457698764, '50/12');
INSERT INTO CreditCard (memberNo, billingNo, name, brand, ccNo, expires) VALUES (12, 3, 'DavideLancer', 'master',4481223432144912, '50/12');
INSERT INTO CreditCard (memberNo, billingNo, name, brand, ccNo, expires) VALUES (13, 2, 'LucyPall', 'visa',6234123413242278, '50/12');
COMMIT;

CREATE TABLE PeerPark.PayPal (
  memberNo      INTEGER,
  billingNo     INTEGER,
  email         EMailType NOT NULL,
  CONSTRAINT PayPal_PK PRIMARY KEY (memberNo, billingNo),
  CONSTRAINT PayPal_BillingAccount_FK FOREIGN KEY (memberNo,billingNo) REFERENCES BillingAccount(memberNo,billingNo)  ON DELETE CASCADE ON UPDATE CASCADE
);

BEGIN TRANSACTION;
INSERT INTO PayPal (memberNo, billingNo, email) VALUES (11, 1, 'abcpay@cat.com');
INSERT INTO PayPal (memberNo, billingNo, email) VALUES (12, 3, 'missknopay@cat.com');
INSERT INTO PayPal (memberNo, billingNo, email) VALUES (13, 2, 'userpaypal@cat.com');
COMMIT;


CREATE TABLE PeerPark.Driver (
  memberNo      INTEGER,
  licenceNo     INTEGER NOT NULL,
  licenceExp    DATE    NOT NULL,
  CONSTRAINT Driver_PK PRIMARY KEY (memberNo),
  CONSTRAINT Driver_Member_FK FOREIGN KEY (memberNo) REFERENCES Member(memberNo)  ON DELETE CASCADE
);

BEGIN TRANSACTION;
INSERT INTO Driver (memberNo, licenceNo, licenceExp) VALUES (11, 63748363, '2050-12-30');
INSERT INTO Driver (memberNo, licenceNo, licenceExp) VALUES (12, 29472362, '2050-12-30');
INSERT INTO Driver (memberNo, licenceNo, licenceExp) VALUES (13, 16328393, '2050-12-30');
COMMIT;
/* Owner is defined as a View further down this SQL script */


CREATE TABLE PeerPark.CarType (
  make           VARCHAR(20),
  model          VARCHAR(20),
  length         INTEGER,
  width          INTEGER,
  height         INTEGER,
  CONSTRAINT CarType_PK PRIMARY KEY (make, model)
 );
 
BEGIN TRANSACTION;
INSERT INTO CarType (make, model, length, width, height) VALUES ('VOLVO', 'V5', 3000, 1800, 1600);
INSERT INTO CarType (make, model, length, width, height) VALUES ('Buick', 'Rando', 2800, 1600, 1400);
INSERT INTO CarType (make, model, length, width, height) VALUES ('Benz', 'X8', 2800, 1600, 1400);
INSERT INTO CarType (make, model, length, width, height) VALUES ('Lanbo', 'AA', 2800, 1600, 1400);
INSERT INTO CarType (make, model, length, width, height) VALUES ('Fort', 'FASTBACK', 2800, 1600, 1400);
COMMIT;

CREATE TABLE PeerPark.ParkTag (
  tagID          INTEGER,
  issuedToMember INTEGER,
  issuedToCar    VARCHAR(30),
  CONSTRAINT ParkTag_PK PRIMARY KEY  (tagID)
);

BEGIN TRANSACTION;
INSERT INTO ParkTag (tagID, issuedToMember, issuedToCar) VALUES (11223, 11, 'no');
INSERT INTO ParkTag (tagID, issuedToMember, issuedToCar) VALUES (11224, 13, 'yes');
INSERT INTO ParkTag (tagID, issuedToMember, issuedToCar) VALUES (11225, 12, 'yes');
COMMIT;

CREATE TABLE PeerPark.Car (
  memberNo       INTEGER,
  name           VARCHAR(30),
  regno          RegoType     UNIQUE,
  make           VARCHAR(20)  NOT NULL,
  model          VARCHAR(20)  NOT NULL,
  currentTag     INTEGER      NOT NULL,
  CONSTRAINT Car_PK PRIMARY KEY   (memberNo, name),
  CONSTRAINT Car_Member_FK FOREIGN KEY (memberNo) REFERENCES Member(memberNo) ON DELETE CASCADE,
  CONSTRAINT Car_CarType_FK FOREIGN KEY (make, model) REFERENCES CarType(make,model) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT Car_CurrentTag_FK FOREIGN KEY (currentTag) REFERENCES ParkTag(tagID) ON DELETE NO ACTION ON UPDATE CASCADE
);

BEGIN TRANSACTION;
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (11, 'MyVOLVO', 'regnoo', 'VOLVO', 'V5', 11223);
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (11, 'MyBenz', 'regnoa', 'Benz', 'X8', 11223);
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (11, 'MyLanbo', 'regnoc', 'Lanbo', 'AA', 11223);
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (12, 'MyBuick','regnon', 'Buick', 'Rando', 11224);
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (12, 'MyFort','regnot', 'Fort', 'FASTBACK', 11224);
INSERT INTO Car (memberNo, name, regno, make, model, currentTag) VALUES (13, 'MyVOLVO','regnob', 'VOLVO', 'V5', 11225);
COMMIT;

/* ALTER TABLE PeerPark.ParkTag
        ADD CONSTRAINT ParkTag_Car_FK FOREIGN KEY (issuedToMember,issuedToCar) REFERENCES Car(memberNo,name)  ON DELETE NO ACTION ON UPDATE CASCADE; */

CREATE TABLE PeerPark.ParkPod (
  deviceID       INTEGER,
  phone          VARCHAR(15),
  CONSTRAINT     ParkPod_PK PRIMARY KEY (deviceID)
);

BEGIN TRANSACTION;
INSERT INTO ParkPod (deviceID, phone) VALUES (101, 284728);
INSERT INTO ParkPod (deviceID, phone) VALUES (102, 293817);
INSERT INTO ParkPod (deviceID, phone) VALUES (103, 393482);
COMMIT;

/* ADDED - location hierarchy */
CREATE TABLE PeerPark.Location (
  locID          INTEGER,
  name           VARCHAR(100),
  type           VARCHAR(10),
  is_at          INTEGER  NULL,
  CONSTRAINT   Location_PK      PRIMARY KEY (locID),
  CONSTRAINT   Location_KEY     UNIQUE(name, is_at),
  CONSTRAINT   Location_IsAt_FK FOREIGN KEY (is_at) REFERENCES Location(locID),
  CONSTRAINT   Location_Type_CHK CHECK (type IN ('street','suburb','area','region','city','state','country'))
);

BEGIN TRANSACTION;
INSERT INTO Location (locID, name,type, is_at) VALUES (1001, 'Kina', 'state', '1001');
INSERT INTO Location (locID, name,type, is_at) VALUES (1003, 'Washinton', 'region', '1001');
INSERT INTO Location (locID, name,type, is_at) VALUES (1005, 'LWsh','city', '1003');
INSERT INTO Location (locID, name,type, is_at) VALUES (1007, 'NewBil','street', '1005');
COMMIT;                                 

CREATE TABLE PeerPark.ParkBay (
  bayID            SERIAL,
  owner            INTEGER,
  site             VARCHAR(50) NOT NULL UNIQUE,  /* NOTE: == name */
  address          VARCHAR(200),
  description      TEXT,             /* ADDED: some description text per bay */
  gps_lat          FLOAT,
  gps_long         FLOAT,
  mapURL           VARCHAR(200),     /* ADDED: we have example data for Google-Maps URLs*/
  located_at       INTEGER NOT NULL,
  width            INTEGER,
  height           INTEGER,
  length           INTEGER,
  pod              INTEGER,
  avail_wk_start   SMALLINT,
  avail_wk_end     SMALLINT,
  avail_wend_start SMALLINT,
  avail_wend_end   SMALLINT,

  CONSTRAINT ParkBay_PK PRIMARY KEY (bayId),
  CONSTRAINT ParkBay_Member_FK   FOREIGN KEY (owner) REFERENCES Member(memberNo),
  CONSTRAINT ParkBay_ParkPod_FK  FOREIGN KEY (pod)   REFERENCES ParkPod(deviceID),
  CONSTRAINT ParkBay_Location_FK FOREIGN KEY (located_at) REFERENCES Location(locID) ON DELETE RESTRICT ON UPDATE CASCADE
);

BEGIN TRANSACTION;
INSERT INTO ParkBay (bayID, owner, site, address, pod, located_at,description, gps_lat, gps_long, mapURL,width,length,height,avail_wk_start,avail_wk_end,avail_wend_start,avail_wend_end) VALUES (896541, 11, 'Library', 'Glebe Point Road', 101, 1001,'Very nice park',456,5689,'https://maps.google.com',45,56,56,9,17,2,3);
INSERT INTO ParkBay (bayID, owner, site, address, pod, located_at,description, gps_lat, gps_long, mapURL,width,length,height,avail_wk_start,avail_wk_end,avail_wend_start,avail_wend_end) VALUES (954673, 13, 'Sydney Uni', 'Camperdown', 102, 1005,'real nice park',456,5689,'https://maps.google.com',45,56,56,9,17,2,3);
INSERT INTO ParkBay (bayID, owner, site, address, pod, located_at,description, gps_lat, gps_long, mapURL,width,length,height,avail_wk_start,avail_wk_end,avail_wend_start,avail_wend_end) VALUES (321567, 12, 'UTS', 'Ultimo', 103, 1007,'REALLY nice park',456,5689,'https://maps.google.com',45,56,56,9,17,2,3);
INSERT INTO ParkBay (bayID, owner, site, address, pod, located_at,description, gps_lat, gps_long, mapURL,width,length,height,avail_wk_start,avail_wk_end,avail_wend_start,avail_wend_end) VALUES (561567, 11, 'BBC', 'BKS', 103, 1001,':D nice park',456,5689,'https://maps.google.com',45,56,56,9,17,2,3);
COMMIT;

ALTER TABLE PeerPark.Member
      ADD CONSTRAINT Member_ParkBay_FK FOREIGN KEY (prefBay) REFERENCES ParkBay(bayID) ON DELETE SET NULL;

CREATE VIEW PeerPark.Owner AS
       SELECT DISTINCT owner AS memberNo FROM PeerPark.ParkBay;

CREATE TABLE PeerPark.Booking (
  bookingID      SERIAL,
  bayID          INTEGER     NOT NULL, 
  bookingDate    DATE        NOT NULL,
  bookingHour    INTEGER     NOT NULL,
  duration       INTEGER     NOT NULL,
  memberNo       INTEGER     NOT NULL,
  car            VARCHAR(30) NOT NULL,

  CONSTRAINT Booking_PK   PRIMARY KEY (bookingID),
  CONSTRAINT Booking_KEY  UNIQUE (bayId, bookingDate, bookingHour),
  CONSTRAINT Booking_Bay_FK FOREIGN KEY (bayID) REFERENCES ParkBay(bayID) ON DELETE CASCADE,
  CONSTRAINT Booking_Car_FK FOREIGN KEY (memberNo,car) REFERENCES Car(memberNo,name)
);

/*
 * example trigger:
 * whenever a BillingAccount gets deleted, then 'compress' the remaining billingNos
 * this assumes ON UPDATE CASCADE FKs on the sub-entity tables
 */
CREATE FUNCTION PeerPark.BillingAccountNrFixer() RETURNS trigger AS
$$
   BEGIN
      UPDATE PeerPark.BillingAccount
         SET billingNo=billingNo-1
       WHERE memberNo  = OLD.memberNo
         AND billingNo > OLD.billingNo;
   END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER BillingAccountDeleteTrigger
       AFTER DELETE ON PeerPark.BillingAccount
       FOR EACH ROW
       WHEN ( OLD.billingNo < 3 )
       EXECUTE PROCEDURE PeerPark.BillingAccountNrFixer();


/* IMPORTANT TODO: */
/* please replace <postgres> with the name of your PostgreSQL login */
/* in the following ALTER USER username SET search_path ... command   */
/* this ensures that the carsharing schema is automatically used when you query one of its tables */
/* it assumes that you have loaded our unidb schema from tutorial in week 6             */
ALTER USER postgres SET search_Path = PeerPark, public, unidb, PeerPark;



/*
 * Some optional, more complex semantic integrity constraints as Assertions
 * as example solution for assignment 2
 */

/*
  -- guarantee at least 1 and max three BillingAccounts per member
  CREATE ASSERTION AssertAccountSize CHECK (
     NOT EXISTS (
                  SELECT memberNo
                    FROM Member LEFT OUTER JOIN BillingAccount USING (memberNo)
                   GROUP BY memberNo
                  HAVING COUNT(billingNo) NOT IN (1,2,3)
                )
     );
*/

/*
  -- guarantee disjointness of BillingAccount-SubTypes
  CREATE ASSERTION AssertAccountSize CHECK (
     NOT EXISTS (
                  SELECT memberNo, billingNo FROM BankAccount
                INTERSECT
                  SELECT memberNo, billingNo FROM CreditCard
                INTERSECT
                  SELECT memberNo, billingNo FROM PayPal
                )
     );
*/
##### SQL Database Requirement
Name: suv_db

Table(1): suv_signups

```
CREATE TABLE IF NOT EXISTS `suv_signups` (
`id` int(1) NOT NULL,
  `timestamp` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `is_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
```

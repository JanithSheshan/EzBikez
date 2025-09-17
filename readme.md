 1. ezbikez/
 2. ├─ config/
 3. │ ├─ config.example.php
 4. │ └─ db.php
 5. ├─ includes/
 6. │ ├─ header.php
 7. │ ├─ footer.php
 8. │ ├─ nav.php
 9. │ └─ helpers.php
10. ├─ public/
11. │ ├─ about.php # About (public)
12. │ ├─ availability.php # Search + available bikes (public)
13. │ ├─ booking.php # Booking creation (requires login)
14. │ ├─ login.php # Auth
15. │ ├─ register.php # Auth
16. │ ├─ logout.php # Auth
17. │ ├─ my-bookings.php # User bookings dashboard
18. │ ├─ assets/
19. │ │ ├─ css/style.css #optional
20. │ │ └─ img/
21. │ └─ uploads/bikes/ # Bike images (writeable)
22. ├─ admin/
23. │ ├─ index.php # Dashboard
24. │ ├─ bikes.php # Add/Edit/Delete bikes
25. │ ├─ bookings.php # Review/Accept/Reject bookings
26. │ ├─ booking-action.php
27. │ ├─ users.php
28. │ ├─ user-details.php
29. │ ├─ bike-add.php
30. │ ├─ bike-delete.php
31. │ └─ bike-edit.php
32. ├─ sql/
33. │ └─ schema.sql
34. ├─ .htaccess # Optional (Pretty URLs / security)
35. └─ index.php # Home (public)
36.  

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



You said:
I want rent scooters and motor bikes true website. based on unawatuna, Galle, Sri Lanka. I put a name for my website name EzBikez and slogan is "Ride Sri Lanka the easy way". I create a logo and It mainly bassed on #4a8a26 and #003132 these two colors. Now I want to create my website. this website should be modern style and relevant rent motorbike and scooters for tourist. main target audience is tourist But also can Loacals rent a bike using this website. you can use html css js and boostrap for this frontend and php for backend mysql for data bases. first hae home page It's access to all. same as about page. then I want add bike availability check page. It should have search bar to search date range. then show availble bikes. each bike should have details card and booking button. if some one press the booking button check the loged user or not. If user already loged direct to booking page otherwise direct to register and logging page. after that booking page show the bike deatils and the total prize for bike to booking dates. after confirm admin recive the booking request then he can accept ar delete the request. otherwise admin can update the bikes add bikes delete bikes, view all bookings. otherwise the user have a page for check his or her persenol bookings with view pending, accept or reject. I am planning to change the booking system. I introduce the three bike category name A,B and C. A bikes more expensive and good quality, B bikes middle price and middle quality, C bikes low quality and low price. the availbility php shows the bikes in category wise and the user can book a catergory. after booking a catergory user receive a bike but it's details can be change. because we give three bike for customer and he or she can choose a one bike from that three in we physicaly meet day. after choosing the bike customer can rent the bike and It not showing in availability page. If availble bikes less than three in any catergory. show a note, "customer should agree with given bike". If no bike in a catergory customer can't book that catergory. 
the admin have few state. booking approve without bike, after selecting bike add bike and confirm booking, after return the bike update the return status.

once confirm the booking with bike remove the bike in availability list, when update the return note again show the bike in availability list.
I want to change all code relevant to this. also I want to change the sql database. give me updated all code file with full php code. 
website with admin dashboard like,

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





 don't use document style to answer this. just give file path with file name and It's code.
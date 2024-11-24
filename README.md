# mcrsf.xk7.net

This repository contains the code behind the website mcrsf.xk7.net, which
lists all the upcoming and read books at the Manchester Sci-fi book club,
as well as our suggestions (and rejected suggestions).

It is populated via a spreadsheet which we manage in Google Docs. rclone
is used to sync the spreadsheet to a local machine, then the import.php
script extracts all the books and converts them into an SQLite database.
The database is then uploaded to a Linux VPS and PHP gets the book data
for each page and converts it into HTML.

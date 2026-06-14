GAME STOP — WINDOWS SETUP
=========================

IMPORTANT
---------
1. Extract the zip to a LOCAL folder, for example:
   C:\GameStop
   (Do NOT run from OneDrive, Desktop sync, or Downloads if possible.)

2. If you see "Access is denied":
   - Right-click runtime\php.exe → Properties
   - Tick "Unblock" at the bottom → OK
   - Run "Game Stop.bat" again

3. Use the LOGIN page directly:
   http://127.0.0.1:8765/login

START THE APP
-------------
Double-click:  windows-launcher\Start Game Stop.vbs
(or Game Stop.bat to see error messages)

TROUBLESHOOTING
---------------
Run:  windows-launcher\Diagnose Game Stop.bat
It shows what is working and what failed.

If page keeps loading:
  1. Close all Game Stop windows
  2. Open Task Manager → end any "php.exe" process
  3. Run "Game Stop.bat" again
  4. Open http://127.0.0.1:8765/login

Logs:
  storage\logs\launcher.log
  storage\logs\laravel.log

LOGIN
-----
Email:    admin@gamestop.local
Password: password

NOTHING TO INSTALL ON WINDOWS
-----------------------------
The zip includes PHP, Laravel, and the database (SQLite).
You do NOT need to install PHP, MySQL, Node, or XAMPP on the Windows PC.
Just extract the zip and run Start Game Stop.vbs.

# ABOUT


## API ENDPOINTS

POST /players : create a player.
PUT /players/{id} : update player's name.
POST /players/{id}/games/ : player's throw dice, outcome gets stored in database.
DELETE /players/{id}/games: deletes ALL plays of a given player (full history) from database.
GET /players: returns ALL players and average success rate for each of them.
GET /players/{id}/games: returns the outcomes of ALL plays of a given player.
GET /players/ranking: returns the average success rate of ALL players... (not the same than average success rate of ALL throws).
GET /players/ranking/loser: returns player with LOWEST success rate.
GET /players/ranking/winner: returns player with HIGHEST success rate..


## DEVELOPER ENVIRONMENT SETUP
1) Hardware : developed with an iMac (Retina 4K, 21.5-inch, Late 2015). Processor : 3,3 GHz Quad-Core Intel Core i7. Operating System : macOS Monterrey 12.7.6
2) Software :
- XAMPP
- Laravel : implemented Bearer Token authentication with Sanctum.
- Postman

Constrains : Sanctum instead of Passport.
I could not install Laravel Passport. My XAMPP php.ini (MacOS) doesn't allow to activate sodium extension, all extensions come pre-built in the package and sodium extension is non-existent in .so file.
I decided to move on with Sanctum instead.


## GITFLOW

I worked on main branch until I had prepared Models and Migrations.
Once having everything set to work with roles and token authentication I created the develop branch.

OK, here we go :
1/ auth-setup : prepare model User and controllers methods for Sanctum token and role check
2/ tdd : start with PHPUnit testing, starting by testing role restricted access to endpoints

main
└── develop
    ├── feature/auth-setup          (Sanctum + roles setup)
    ├── feature/tdd                 (prepare tdd for all controller methods)
    ├── feature/game-actions       (authenticated gamer actions : roll dice, show plays, delete plays, update name and/or nickname)
    └── feature/admin-statistics    (store, index, ranking, winner, loser)


### GITGLOW STRATEGY - WORKFLOW FOR SOLO DEVELOPER

FOR EACH feature/name branch :

Being on develop branch :
git checkout -b feature/name develop
(or just git checkout -b feature/name)

Branch is created and you auto checkout to it.

On feature/name branch :
1- git commit -m "commit description"
2- git push origin feature/name (first time : git push -u origin feature/name)
(repeat 1 and 2 as many times you want/need until ready to merge with develop)
3- git checkout develop

On develop branch
4- git merge feature/name
5- git push origin develop
6- git branch -d feature/name

The feature/name branch is kept intact on origin but deleted locally.
In case you need to work on same feature again :

7- git checkout -b feature/name develop
(branch feature/name is recreated)
8- Repeat process from 1 to 6

When recreating the feature branch with the same name and pushing it to origin (git push origin feature/name)

A ChatGPT advise :
Over time you accumulate stale remote-tracking branches locally. Clean them from time to time :
git fetch --prune


### GITGLOW STRATEGY - WORKFLOW FOR DEVELOPER'S TEAM

1- Update develop locally and create branch feature/name
git checkout develop
git pull origin develop
git checkout -b feature/name

2- Push feature/name branch to origin
git push -u origin feature/name

3- Develop and commit changes. Push to origin
git add .
git commit -m "Feature work"
git push

4- Merge feature/name into develope remotely
- Open Pull Request (PR) in GitHub
- Review, approve and merge the PR

5- Delete Feature Branch
git branch -d feature/name (Local)
git push origin --delete feature/name (Remote)

6- Update develop locally
git checkout develop
git pull origin develop


## REGARDING REGISTRY AND AUTHENTICATION (AuthController)

* Sign-up (register method) : No permission (no Bearer Token) needed for register in the app.
* Sign-in (login method) : Only registered users can log-in. A token is created in personal_access_token table.
A user can sign-in more than once and have several active tokens... User can log-in from several devices.
* Sign-out (logout method) : Only logged-in users can log-out (Bearer Token needed). When logging out ALL tokens are deleted from personal_access_token table. All devices connected are logged out.

Advantages:
- Enhanced security: By invalidating all tokens upon logout, the system ensures complete account security across all devices.
- Simplified token management: The server doesn't need to maintain multiple active tokens per user.
Drawbacks:
- User inconvenience: Users must re-authenticate on all devices after logging out from any single device.
- Potential disruption: Active sessions on other devices are terminated without warning.

Having both functions ("register" in AuthController and "store" in PlayerController) makes sense since they serve different purposes:

1. REGISTER in AuthController

    Purpose: Handles user registration via a public-facing form.
    Role: Typically, it:
        Validates user input.
        Creates a user account.
        Sends a confirmation email (if email verification is implemented).
    Scope: It’s used during initial user signup.

2. STORE in PlayerController

    Purpose: Handles creating new players, usually by an admin or an authenticated user with permission to access the endpoint.
    Role: Typically, it:
        Requires the user to be authenticated (e.g., an admin adding players).
        Assumes the email is already verified if email verification is implemented.
    Scope: It’s restricted to specific roles (e.g., admin).

Key Differences
Aspect	        REGISTER (AuthController)	        STORE (PlayerController)
Accessibility	Public-facing (unauthenticated)     Restricted to specific roles (e.g., admin)
Verification	Usually sends email confirmation	Assumes email is already verified
Use Case	    User signing up for the app         Admin creating new players


## PHP UNIT : FEATURE TESTS

The migrations of the production environment are set for MariaDB. The testing environment is set for SQLite.

AuthControllerTest:
    Repeats user registration and token creation for each test.
    Prioritizes test independence over DRY.

AdminControllerTest:
    Uses setUp for centralized user and token creation.
    Prioritizes DRY while reusing shared setup for all tests.

PlayControllerTest :
    Uses.
    Prioritizes.


## TUTORIALS & OTHER RESOURCES

In convenient chronological order to prepare for the API development :

POSTMAN SERIE : INTRO TO APIs (3 episodios)
https://www.youtube.com/playlist?list=PLM-7VG-sgbtA-MUiVgE-SwK_RkYgesikH

HITESH CHOUDHARY : What is Postman and why everyone use it ?
https://youtu.be/A36VQFdIAkI?

POSTMAN SERIE : INTRO TO POSTMAN (6 episodios)
https://youtube.com/playlist?list=PLM-7VG-sgbtAgGq_pef5y_ruIUBPpUgNJ&si=72l03SQqGNsFQsjq

CHAI ASSERTION LIBRARIES : To design PostMan requests9882255$
https://www.chaijs.com/guide/
https://www.chaijs.com/

NOT GOOD - POSTMAN : WHAT IS A REST API? (NOT SO GOOD - LET'S KEEP IT FOR THE MOMENT)
https://www.youtube.com/watch?v=PfujVETI-i4

FAZT CODE : Crea una REST API CRUD en Laravel desde Cero
https://www.youtube.com/watch?v=eLI8c_NtkBk

ENVATO TUTS+ : How to Build a REST API With Laravel: PHP Full Course
https://www.youtube.com/watch?v=YGqCZjdgJJk

LEARN WITH JON : Laravel 11 API CRUD app with Authentication using Laravel Sanctum course 2024 | Part 1/2 (2 episodios)
https://youtu.be/LmMJB3STuU4?
https://youtu.be/7pCDK321ckE? : IMPORTANT FROM MINUTE 21:00

ALFREDO MENDOZA : CRUD aplicando TDD en Laravel
https://youtu.be/_GwqxAi_ly0?si=kWJudLybfilf3aE6

https://www.youtube.com/watch?v=UjA-16diixc
https://www.youtube.com/watch?v=_t9l2TwGioc


LARAPHANT : Laravel Passport Rest API Authentication Tutorial
https://youtu.be/Qykdok80I9U

FAZT CODE : Laravel & MySQL Despliegue en Railway desde cero
https://youtu.be/uU7tWbyqKXc?si=n9HcZJwy10w9N4Sw

NOT GOOD - Hitesh Choudhary : Get Postman in your VSCode (JUST AN INTRO WHEN THE EXTENSION WAS JUST RELEASED)
https://youtu.be/zLdcmJDIXt8?si=AMmllNrj_3pbPWoT

https://youtu.be/A36VQFdIAkI?si=FfpasOVFAyXszfIy

https://www.youtube.com/watch?v=dfWEhh0mVYc&list=PLh-F6-XbduO_PidlrQWUTCW0PitcBRV8Q





https://www.souysoeng.com/2024/07/laravel-11-passport-rest-api.html?m=1
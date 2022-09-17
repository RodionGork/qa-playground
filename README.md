# QA playground

Here comes a small web-application providing user register / login / show list features.

You need to use docker to launch it, and then test both manually and with some kind of automation to find some
intentional (and perhaps unintentional) bugs!

### Deploy

1. Install docker, use docker manual suitable for your preferred OS, that shouldn't be difficult.

2. Download and unpack (zip) or clone (with git) the project somewhere at your machine.

3. Open the console (command line) window and change directory to project folder.

4. Launch the docker container with command  
    `docker run --rm -p 8008:80 -v $(pwd):/var/www/html --name qa-playground php:7.4-apache`.  
    First time it takes some time to download necessary docker image, on next it launches immediately.
    You'll observe here server logs after launch.

5. Switch to your preferred internet browser and navigate to [http://localhost:8008/](http://localhost:8008).

6. To shutdown the server, switch back to the console window and press `Ctrl-C`.

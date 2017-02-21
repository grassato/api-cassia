### Installation

##### https://github.com/EugenMayer/docker-sync

```
gem install docker-sync
brew install fswatch
```


### Usage

Copy a docs/docker-sync.yml configuration in your project root, see configuration
Adjust your docker-compose.yml as explained here
Start the syncronisation with:

```
 docker-sync start 
```
 
 and let docker-sync run in the background

In a new shell run after you started docker-sync docker-compose up

##### More in :https://github.com/EugenMayer/docker-sync/wiki
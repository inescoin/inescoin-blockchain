// Load needed modules
var fs = require('fs');
var cluster = require('cluster');
var os = require('os');

// Load log system
require('./logger.js');

var logSystem = 'master';
if (cluster.isWorker) {
  switch(process.env.workerType) {
        case 'messenger':
            require('./server.js');
            break;
   }
}

if (cluster.isMaster) {
	/**
	 * Start
	 **/
	(function init(){
	    spawnMessengerWorkers();
	})();
}
/**
 * Spawn messenger workers
 **/
function spawnMessengerWorkers(){
    var numForks = 1;
    var messengerWorkers = {};

    var createMessengerWorker = function(forkId){
    	if (!cluster.fork) {
    		return;
    	}
      var worker = cluster.fork({
          workerType: 'messenger',
          forkId: forkId
      });
      worker.forkId = forkId;
      worker.type = 'messenger';
      messengerWorkers[forkId] = worker;
      worker.on('exit', function(code, signal){
          log('error', logSystem, 'Messenger fork %s died, spawning replacement worker...', [forkId]);
          setTimeout(function(){
              createMessengerWorker(forkId);
          }, 2000);
      }).on('message', function(msg){
          switch(msg.type){
              case 'banIP':
                  Object.keys(cluster.workers).forEach(function(id) {
                      if (cluster.workers[id].type === 'messenger'){
                          cluster.workers[id].send({type: 'banIP', ip: msg.ip});
                      }
                  });
                  break;
          }
      });
    };

    var i = 1;
    var spawnInterval = setInterval(function(){
        createMessengerWorker(i.toString());
        i++;
        if (i - 1 === numForks){
            clearInterval(spawnInterval);
            log('info', logSystem, 'Messenger spawned on %d thread(s)', [numForks]);
        }
    }, 10);
}

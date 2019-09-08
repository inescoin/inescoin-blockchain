# Inescoin Blockchain - Encrypted messenger with unfalsifiable transactions

1 - Install node with ansible (Ubuntu)

```
  git clone https://github.com/inescoin/inescoin-ansible
  
  # Update your /etc/ansible/hosts file  with remote IP
  cd inescoin-ansible && ansible-playbook inescoin.yml
   
  # New systemctrl inescoin-node.service is now available
  # /etc/systemd/system/inescoin-node.service
```

2 - Start inescoin node

```
  # With service
  systemctrl start inescoin-node.service
  
  # With bin
  cd /opt/
  src/bin/inescoin-node --rpc-bind-port=8087 --p2p-bind-port=3031 --network=MAINNET --prefix=moon --rpc-bind-ip=IP --p2p-bind-ip=IP
  
```

3 - Monitoring
```
  journalctrl -u inescoin-node.service -f
```



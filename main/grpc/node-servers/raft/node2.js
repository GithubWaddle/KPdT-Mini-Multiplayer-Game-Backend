const RaftServer = require('./RaftServer');

const server = new RaftServer(
  'raft_node_2',
  4002,
  ['http://raft_node_1:4001', 'http://raft_node_3:4003']
);

server.start();

const http = require('http');
const { RaftNode } = require('./RaftNode');

class RaftServer {
  constructor(nodeId, port, peers) {
    this.node = new RaftNode(nodeId, peers);
    this.port = port;

    this.node.on('leader',   () => console.log(`[${nodeId}] Now handling as LEADER`));
    this.node.on('follower', () => console.log(`[${nodeId}] Stepped down to FOLLOWER`));
  }

  start() {
    const server = http.createServer((req, res) => {
      let body = '';

      req.on('data', chunk => body += chunk);
      req.on('end',  () => {
        try {
          const data = body ? JSON.parse(body) : {};
          const response = this.handleRequest(req.url, req.method, data);

          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify(response));

        } catch (err) {
          res.writeHead(500);
          res.end(JSON.stringify({ error: err.message }));
        }
      });
    });

    server.listen(this.port, () => {
      console.log(`[${this.node.nodeId}] Raft HTTP server on port ${this.port}`);
    });
  }

  handleRequest(url, method, data) {
    // POST /vote — another candidate asking for our vote
    if (url === '/vote' && method === 'POST') {
      return this.node.handleVoteRequest(data.candidateId, data.term);
    }

    // POST /heartbeat — leader proving it's alive
    if (url === '/heartbeat' && method === 'POST') {
      return this.node.handleHeartbeat(data.leaderId, data.term);
    }

    // GET /status — PHP app polls this to find out who's leader
    if (url === '/status' && method === 'GET') {
      return this.node.getStatus();
    }

    return { error: 'Unknown route' };
  }
}

module.exports = RaftServer;

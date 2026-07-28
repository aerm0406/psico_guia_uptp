const fs = require('fs');
const readline = require('readline');

async function processLineByLine() {
  const fileStream = fs.createReadStream('C:\\Users\\admin\\.gemini\\antigravity-ide\\brain\\6b653e6e-e01c-433b-89e2-33601df447a1\\.system_generated\\logs\\transcript_full.jsonl');

  const rl = readline.createInterface({
    input: fileStream,
    crlfDelay: Infinity
  });

  for await (const line of rl) {
    if (line.includes('index.blade.php') && line.includes('multi_replace')) {
      try {
        const json = JSON.parse(line);
        if (json.tool_calls && json.tool_calls[0] && json.tool_calls[0].parameters && json.tool_calls[0].parameters.ReplacementChunks) {
          json.tool_calls[0].parameters.ReplacementChunks.forEach(chunk => {
            console.log('\n--- REPLACEMENT ---\n' + chunk.ReplacementContent);
          });
        }
      } catch (err) {}
    }
  }
}

processLineByLine();

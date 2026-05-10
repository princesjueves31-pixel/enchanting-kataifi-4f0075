const fs = require("fs");

const file = "models/overall4_optimized.glb";
const buffer = fs.readFileSync(file);

if (buffer.toString("ascii", 0, 4) !== "glTF") {
  throw new Error("Not a GLB file.");
}

const chunks = [];
let offset = 12;

while (offset < buffer.length) {
  const length = buffer.readUInt32LE(offset);
  const type = buffer.toString("ascii", offset + 4, offset + 8);
  chunks.push({
    length,
    type,
    start: offset + 8,
    end: offset + 8 + length
  });
  offset += 8 + length;
}

const jsonChunk = chunks.find(chunk => chunk.type === "JSON");
if (!jsonChunk) {
  throw new Error("GLB has no JSON chunk.");
}

const json = JSON.parse(
  buffer.toString("utf8", jsonChunk.start, jsonChunk.end).trim()
);

let fixed = 0;

for (const texture of json.textures || []) {
  const webpSource = texture.extensions?.EXT_texture_webp?.source;

  if (texture.source === undefined && Number.isInteger(webpSource)) {
    texture.source = webpSource;
    fixed += 1;
  }

  if (texture.source === undefined && (json.images || []).length > 0) {
    texture.source = 0;
    fixed += 1;
  }
}

const jsonText = JSON.stringify(json);
const jsonBytes = Buffer.from(jsonText, "utf8");
const jsonPadding = (4 - (jsonBytes.length % 4)) % 4;
const newJsonChunk = Buffer.concat([
  jsonBytes,
  Buffer.alloc(jsonPadding, 0x20)
]);

const otherChunks = chunks
  .filter(chunk => chunk !== jsonChunk)
  .map(chunk => buffer.subarray(chunk.start - 8, chunk.end));

const totalLength =
  12 +
  8 +
  newJsonChunk.length +
  otherChunks.reduce((total, chunk) => total + chunk.length, 0);

const header = Buffer.alloc(12);
header.write("glTF", 0, "ascii");
header.writeUInt32LE(2, 4);
header.writeUInt32LE(totalLength, 8);

const jsonHeader = Buffer.alloc(8);
jsonHeader.writeUInt32LE(newJsonChunk.length, 0);
jsonHeader.write("JSON", 4, "ascii");

fs.writeFileSync(
  file,
  Buffer.concat([header, jsonHeader, newJsonChunk, ...otherChunks], totalLength)
);

console.log(`Fixed texture sources: ${fixed}`);
console.log(`Updated ${file}`);

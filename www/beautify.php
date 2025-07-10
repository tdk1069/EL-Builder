<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>LPC Beautifier</title>
<style>
  body { font-family: monospace; margin: 1em; background: #1e1e1e; color: #d4d4d4; }
  textarea { width: 100%; height: 400px; background: #252526; color: #d4d4d4; border: 1px solid #333; padding: 1em; box-sizing: border-box; font-family: monospace; font-size: 14px; }
  button { margin-top: 1em; padding: 0.5em 1em; font-size: 1em; }
</style>
</head>
<body>

<h1>LPC Beautifier</h1>
<textarea id="input" placeholder="Paste your LPC code here..."></textarea>
<button onclick="beautify()">Beautify</button>
<textarea id="output" placeholder="Beautified code appears here..."></textarea>

<script>
function beautify() {
  const input = document.getElementById('input').value;
  const lines = input.split('\n');

  let indentLevel = 0;
  const indentStr = '    '; // 4 spaces

  // Helper to join multiline adjacent strings into one string
function joinAdjacentStrings(lines) {
  const result = [];
  let buffer = null;

  // Wrap text at maxLength, splitting on spaces where possible
  function wrapText(text, maxLength) {
    const words = text.split(/(\s+)/); // keep spaces in output array
    let lines = [];
    let currentLine = '';

    for (let w of words) {
      // If adding the word exceeds maxLength, push currentLine and start new
      if ((currentLine + w).length > maxLength) {
        if (currentLine.trim()) lines.push(currentLine.trimEnd());
        // If word itself longer than maxLength, break inside word (rare)
        if (w.length > maxLength) {
          // split long word into chunks
          for (let i = 0; i < w.length; i += maxLength) {
            lines.push(w.slice(i, i + maxLength));
          }
          currentLine = '';
        } else {
          currentLine = w;
        }
      } else {
        currentLine += w;
      }
    }
    if (currentLine.trim()) lines.push(currentLine.trimEnd());
    return lines;
  }

  for (let line of lines) {
    const trimmed = line.trim();
    const stringLine = /^(\s*(".*?"\s*)+;?\s*)$/;
    if (stringLine.test(line)) {
      const semi = trimmed.endsWith(';');
      let cleanLine = trimmed;
      if (semi) cleanLine = cleanLine.slice(0, -1);

      const matches = [...cleanLine.matchAll(/"([^"]*)"/g)];
      const combined = matches.map(m => m[1]).join('');
      buffer = (buffer || '') + combined;

      if (semi) {
        // Wrap at 78 columns inside quotes
        const wrappedLines = wrapText(buffer, 78);
        for (let i = 0; i < wrappedLines.length; i++) {
          const prefix = indentStr.repeat(indentLevel);
          // add semicolon only to last line
          const suffix = (i === wrappedLines.length - 1) ? ';' : '';
          result.push(prefix + `"${wrappedLines[i]}"` + suffix);
        }
        buffer = null;
      }
    } else {
      if (buffer !== null) {
        const wrappedLines = wrapText(buffer, 78);
        for (let i = 0; i < wrappedLines.length; i++) {
          const prefix = indentStr.repeat(indentLevel);
          const suffix = (i === wrappedLines.length - 1) ? ';' : '';
          result.push(prefix + `"${wrappedLines[i]}"` + suffix);
        }
        buffer = null;
      }
      result.push(line);
    }
  }
  if (buffer !== null) {
    const wrappedLines = wrapText(buffer, 78);
    for (let i = 0; i < wrappedLines.length; i++) {
      const prefix = indentStr.repeat(indentLevel);
      const suffix = (i === wrappedLines.length - 1) ? ';' : '';
      result.push(prefix + `"${wrappedLines[i]}"` + suffix);
    }
    buffer = null;
  }
  return result;
}

  // First, join adjacent strings across lines
  let processedLines = joinAdjacentStrings(lines);

  const outputLines = [];

  processedLines.forEach(line => {
    const trimmed = line.trim();

    // Decrease indent if line starts with }
    if (trimmed.startsWith('}')) {
      indentLevel = Math.max(indentLevel - 1, 0);
    }

    // Apply indent
    outputLines.push(indentStr.repeat(indentLevel) + trimmed);

    // Increase indent if line ends with {
    // Also increase indent after lines ending with these keywords: if, else, for, while, switch, do
    if (trimmed.endsWith('{') ||
        /^(if|else|for|while|switch|do)\b.*\{$/.test(trimmed)) {
      indentLevel++;
    }
  });

  document.getElementById('output').value = outputLines.join('\n');
}
</script>

</body>
</html>

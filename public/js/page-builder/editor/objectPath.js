export function setByPath(target, path, value) {
  const keys = path.split('.');
  let cursor = target;

  keys.slice(0, -1).forEach((key) => {
    cursor = cursor[key];
  });

  cursor[keys.at(-1)] = value;
}

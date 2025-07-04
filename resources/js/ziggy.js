const Ziggy = {
  url: 'https://sxs.tg25.win',
  port: null,
  defaults: {},
  routes: {
    'machine.update': { uri: 'machines/update/{id}', methods: ['PATCH'] },
    // 其他路由...
  }
};
export { Ziggy };

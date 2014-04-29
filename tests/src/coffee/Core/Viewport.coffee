Ext.define 'Core.Viewport',

  extend: 'Ext.Viewport'
  requires: ['Core.Child']
  mixins: ['Core.Mixin']

  initComponent: ->
    Ext.create 'Core.Child'
export default {
  methods: {
    getSettingValue(key, context = {}, options = {}) {
      const setting = this.settings?.[key]
      if (!setting) {
        return ''
      }

      if (options.to === 'device' && context.device?.device_id) {
        return setting.device_settings?.[context.device.device_id] ?? ''
      }

      const site = context.site || context.device?.site || null
      const device = context.device || null
      const siteId = site?.ds_id ?? device?.device_ds_id ?? null
      const protocolId = site?.ds_protocol_id ?? device?.site?.ds_protocol_id ?? null
      const accountId = this.accountId

      if (siteId !== null && setting.ds_settings?.[siteId] !== undefined) {
        return setting.ds_settings[siteId]
      }

      if (siteId !== null && setting.label_settings?.[siteId] !== undefined) {
        return setting.label_settings[siteId]
      }

      if (accountId !== null && protocolId !== null && setting.acc_mod_settings?.[accountId]?.[protocolId] !== undefined) {
        return setting.acc_mod_settings[accountId][protocolId]
      }

      if (protocolId !== null && setting.mod_settings?.[protocolId] !== undefined) {
        return setting.mod_settings[protocolId]
      }

      if (accountId !== null && setting.acc_settings?.[accountId] !== undefined) {
        return setting.acc_settings[accountId]
      }

      return setting.settings ?? ''
    },

    getSettingLevel(key, context = {}) {
      const setting = this.settings?.[key]
      if (!setting) {
        return ''
      }

      const site = context.site || context.device?.site || null
      const device = context.device || null
      const siteId = site?.ds_id ?? device?.device_ds_id ?? null
      const protocolId = site?.ds_protocol_id ?? device?.site?.ds_protocol_id ?? null
      const accountId = this.accountId

      if (siteId !== null && setting.ds_settings?.[siteId] !== undefined) {
        return this.trans('Site settings')
      }

      if (siteId !== null && setting.label_settings?.[siteId] !== undefined) {
        return this.trans('Label settings') + ` (${setting.label_sources?.[siteId] || 'Label'})`
      }

      if (accountId !== null && protocolId !== null && setting.acc_mod_settings?.[accountId]?.[protocolId] !== undefined) {
        return this.trans('Account module settings')
      }

      if (protocolId !== null && setting.mod_settings?.[protocolId] !== undefined) {
        return this.trans('Module settings')
      }

      if (accountId !== null && setting.acc_settings?.[accountId] !== undefined) {
        return this.trans('Account settings')
      }

      if (setting.settings !== undefined) {
        return this.trans('Root settings')
      }

      return ''
    }
  }
}
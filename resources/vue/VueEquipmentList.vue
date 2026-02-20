<template>
  <div class="test">

    <div v-for="site in sites" :key="site.ds_id" class="site-box">

      <!--NORMAL SITE-->
<!--      <a :href="'/device-site/'+site.ds_id" @click.prevent class="selectable-link" draggable="false">-->
      <a class="selectable-link" :href="'/device-site/'+site.ds_id" draggable="false" @click.prevent>
      <div class="site-row selectable-content" :style="{'height': (activeLabels && site.labels && site.labels.length > 0) ? '8rem' : '5rem', 'cursor': 'pointer' }" v-if="!site.edited" @click="navigateToSite(site)">

        <!--1ST ROW-->
        <div class="flex justify-between items-center" style="margin-bottom: 1.2rem">

          <!-- LEFT SITE ICONS -->
          <div class="flex items-start" style="width: 27%">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.success }">power</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Enabled') }}</span>
            </span>
          </div>

          <div class="gr-6" style="width: 150%">
            <!--NORMAL SITE NAME-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'ds_name')">building</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site Name') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['sitename_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.ds_name }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitename_'+site.ds_id)" @mouseleave="hideTooltip('sitename_'+site.ds_id)">{{ site.ds_name }}</span>
            </div>

            <!--NORMAL ADDRESS-->
            <div class="gs-2 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'addressOneLine')">placemark</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Address') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['address_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.addressOneLine }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('address_'+site.ds_id)" @mouseleave="hideTooltip('address_'+site.ds_id)">{{ site.addressOneLine }}</span>
            </div>

            <!--NORMAL PROTOCOL-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'siteProtocol')">gear_alt</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site Protocol') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['siteprotocol_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.siteProtocol }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('siteprotocol_'+site.ds_id)" @mouseleave="hideTooltip('siteprotocol_'+site.ds_id)">{{ site.siteProtocol }}</span>
            </div>

            <!--NORMAL CUSTOM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.customFields[0]?.value))">{{ site.customFields[0]?.icon || 'info' }}</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.customFields[0]?.name || trans('Custom field')+' 1' }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['custom1_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.customFields[0]?.value || '' }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom1_'+site.ds_id)" @mouseleave="hideTooltip('custom1_'+site.ds_id)">{{ site.customFields[0]?.value || '' }}</span>
            </div>

            <!--NORMAL CUSTOM 2-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.customFields[1]?.value))">{{ site.customFields[1]?.icon || 'info' }}</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.customFields[1]?.name || trans('Custom field')+' 2' }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['custom2_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.customFields[1]?.value || '' }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom2_'+site.ds_id)" @mouseleave="hideTooltip('custom2_'+site.ds_id)">{{ site.customFields[1]?.value || '' }}</span>
            </div>
          </div>

          <!--1ST ROW SITE RIGHT ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="gs-1 flex items-center justify-end" v-if="!actionsForbidden.includes('rightSiteIcons')">

              <span class="icon-wrapper tt" @click.prevent.stop="!site.showComments && showComments(site)" :style="{ 'cursor': site.showComments ? 'default' : 'pointer' }">
                <i class="f7-icons icon default icon-sm tts" :style="{'background-color': (site.showComments ? colors.empty : colors.button), 'color': 'white' }">bubble_left</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Show comments') }}</span>
              </span>

              <span class="icon-wrapper tt" @click.prevent.stop="showHistory(site)" :style="{ 'cursor': site.showHistory === true ? 'default' : 'pointer' }">
                <i class="f7-icons icon default icon-sm tts cursor-pointer" :style="{'background-color': site.showHistory === true ? colors.empty : colors.button, 'color': 'white' }">calendar</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Show history') }}</span>
              </span>

              <a :href="!isEmpty(site.ds_link) ? sanitizeLink(site.ds_link) : '#'"
                 @click.stop="isEmpty(site.ds_link) ? $event.preventDefault() : ''"
                 target="_blank"
                 rel="noopener noreferrer"
                 :style="{ 'cursor': isEmpty(site.ds_link) ? 'default' : 'pointer' }">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts" :style="{'background-color': (isEmpty(site.ds_link) ? colors.empty : colors.button), 'color': 'white' }">link</i>
                  <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.ds_link ?? trans('No link attached') }}</span>
                </span>
              </a>

              <span class="icon-wrapper tt" @click.prevent.stop="startEdit(site)" v-if="!actionsForbidden.includes('edit')">
                <i class="f7-icons icon default icon-sm tts cursor-pointer" style="background-color: #8faadc; color: white;">square_pencil</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Edit') }}</span>
              </span>
            </div>
          </div>
        </div>

        <!--2ND ROW-->
        <div class="flex justify-between items-center" style="margin-bottom: 1.2rem">

          <!--2ND ROW LEFT SITE ICONS EMPTY -->
          <div class="flex items-start elip" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">
            <!--NORMAL SIP-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'sip')">phone</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Sip') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['sip_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.sip }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('sip_'+site.ds_id)" @mouseleave="hideTooltip('sip_'+site.ds_id)">{{ site.sip }}</span>
            </div>

            <!--NORMAL SIM-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'sim')">phone</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Sim') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['sim_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.sim }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('sim_'+site.ds_id)" @mouseleave="hideTooltip('sim_'+site.ds_id)">{{ site.sim }}</span>
            </div>

            <!--NORMAL PBX-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'pbx')">phone</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pbx') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['pbx_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.pbx }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('pbx_'+site.ds_id)" @mouseleave="hideTooltip('pbx_'+site.ds_id)">{{ site.pbx }}</span>
            </div>

            <!--NORMAL PSTN-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'pstn')">phone</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pstn') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['pstn_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.pstn }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('pstn_'+site.ds_id)" @mouseleave="hideTooltip('pstn_'+site.ds_id)">{{ site.pstn }}</span>
            </div>

            <!--NORMAL ALARM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'alarmNumber')">bell</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['alarmnumber_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-top bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.alarmNumber }} ({{ trans('Level') }}: <span style="color: orange;">{{ site.alarmNumberLevel }}</span>)</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('alarmnumber_'+site.ds_id)" @mouseleave="hideTooltip('alarmnumber_'+site.ds_id)">{{ site.alarmNumber }}</span>
            </div>

            <!--NORMAL PERIODICAL 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'periodicalNumber')">arrow_clockwise</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['periodicalnumber_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-top bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.periodicalNumber }} ({{ trans('Level') }}: <span style="color: orange;">{{ site.periodicalNumberLevel }}</span>)</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('periodicalnumber_'+site.ds_id)" @mouseleave="hideTooltip('periodicalnumber_'+site.ds_id)">{{ site.periodicalNumber }}</span>
            </div>
          </div>

          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="gs-1 flex items-center justify-end" v-if="!actionsForbidden.includes('openModal')">

              <span class="icon-wrapper tt" @click.prevent.stop="openSiteCustomFields(site)" :style="{ 'cursor': 'pointer' }">
                <i class="f7-icons icon default icon-sm tts" :style="{'background-color': colors.button, 'color': 'white' }">list_bullet_below_rectangle</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Show custom fields / settings') }}</span>
              </span>

<!--              <a :href="'/device-site/'+site.ds_id">-->
<!--                <span class="icon-wrapper tt">-->
<!--                  <i class="f7-icons icon default icon-sm tts cursor-pointer"-->
<!--                     style="background-color: #8faadc;-->
<!--                     color: white;">arrow_up_right-->
<!--                  </i>-->
<!--                  <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"-->
<!--                        style="width: max-content; zoom: 1.2;">{{ trans('Go to Site') }}</span>-->
<!--                </span>-->
<!--              </a>-->
            </div>
          </div>

        </div>

                <!--3RD ROW (Only shown if there are labels)-->
        <div v-if="activeLabels && site.labels && site.labels.length > 0" class="flex justify-between items-center">
          <div class="flex items-start elip" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">
            <!--NORMAL LABELS-->
            <div class="gs-12 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'labels')">rectangle_on_rectangle_angled</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Labels') }}</span>
              </span>
              <div class="flex items-center ml-4" style="flex-wrap: nowrap; overflow: hidden; height: 2rem;">
                <span v-for="item in getDisplayLabelsForSite(site.ds_id)" :key="itemKey(item)"
                 style="
                    background-color: rgb(236 236 236);
                    padding-inline: 0.6rem;
                    border-radius: 0px;
                    box-shadow: 1px 1px 1px 1px #e6c4c473;
                    margin-right: 0.8rem;
                    white-space: nowrap;
                    display: inline-flex;
                    align-items: center;
                    height: 1.6rem;
                    font-size: 1rem;">{{ item.name }}</span>
                </span>
              </div>
            </div>
          </div>

          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%"></div>
        </div>


      </div>
      </a>


      <!--EDITED SITE-->
      <div class="site-row" style="height: 8rem;" v-if="site.edited">

        <!--EDITED 1ST ROW-->
        <div class="flex justify-between items-center" style="margin-bottom: 1.2rem">

          <!-- EDITED LEFT SITE ICONS -->
          <div class="flex items-start elip" style="width: 27%">
            <!--EDITED SITE POWER-->
            <div class="flex items-start">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.success }">power</i>
                <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site enabled') }}</span>
              </span>
            </div>

          </div>

          <div class="gr-6" style="width: 150%">

            <!--EDITED SITE NAME-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.ds_name))">building</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site Name') }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Site name')" v-model="site.cloned.ds_name">
            </div>

            <!--EDITED ADDRESS-->
            <div class="gs-2 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.address))">placemark</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Street') + ' / ' + trans('Zip') + ' / ' + trans('City') + ' / ' + trans('Country') }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Street')" v-model="site.cloned.address.street">&nbsp;
              <span class="asterisk_input_far" v-if="isFieldRequired('address', site)"></span>
              <input class="input-edit elip cursor-default" :placeholder="trans('Zip')" v-model="site.cloned.address.zip">&nbsp;
              <span class="asterisk_input_far" v-if="isFieldRequired('address', site)"></span>
              <input class="input-edit elip cursor-default" :placeholder="trans('City')" v-model="site.cloned.address.city">&nbsp;
              <span class="asterisk_input_far" v-if="isFieldRequired('address', site)"></span>
              <select class="input-edit select-edit elip cursor-default" v-model="site.cloned.address.countryId">
                <option value="">{{ trans('Select country') }}</option>
                <option v-for="([id, country]) in countries" :key="id" :value="id">{{ country }}</option>
              </select>
              <span class="asterisk_input_tiny" v-if="isFieldRequired('address', site)"></span>
            </div>

            <!--EDITED PROTOCOL-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'siteProtocol')">gear_alt</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site Protocol') }}</span>
              </span>
              <span class="tt-vue">
                <span v-show="tooltips['siteprotocol_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.siteProtocol }}</span>
              </span>
              <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('siteprotocol_'+site.ds_id)" @mouseleave="hideTooltip('siteprotocol_'+site.ds_id)">{{ site.siteProtocol }}</span>
            </div>

            <!--EDITED CUSTOM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.clonedCustomFields[0].value), site.clonedCustomFields[0].isConfigured)">{{ site.clonedCustomFields[0].icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.clonedCustomFields[0].name || trans('Custom field')+' 1' }}</span>
              </span>
              <input v-if="site.clonedCustomFields[0].isConfigured" class="input-edit ml-4 elip cursor-default" :placeholder="site.clonedCustomFields[0].name || trans('Custom field')+' 1'" v-model="site.clonedCustomFields[0].value">
            </div>

            <!--EDITED CUSTOM 2-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.clonedCustomFields[1].value), site.clonedCustomFields[1].isConfigured)">{{ site.clonedCustomFields[1].icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.clonedCustomFields[1].name || trans('Custom field')+' 2' }}</span>
              </span>
              <input v-if="site.clonedCustomFields[1].isConfigured" class="input-edit ml-4 elip cursor-default" :placeholder="site.clonedCustomFields[1].name || trans('Custom field')+' 2'" v-model="site.clonedCustomFields[1].value">
            </div>
          </div>

          <!--EDITED 1ST ROW SITE RIGHT ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
             <div class="gs-1 flex items-center justify-end">
              <span class="icon-wrapper tt" @click.prevent.stop="saveEdit(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: green;">checkmark_alt
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Save changes') }}</span>
              </span>

              <span class="icon-wrapper tt" @click.prevent.stop="cancelEdit(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: red;">xmark
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Discard changes') }}</span>
              </span>
            </div>
          </div>
        </div>

        <!--EDITED 2ND ROW-->
        <div class="flex justify-between items-center" style="margin-bottom: 1.2rem">

          <!--EDITED 2ND ROW LEFT SITE ICONS EMPTY -->
          <div class="flex items-start elip" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">
            <!--EDITED SIP-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.sip))">phone</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Sip') }}</span>
              </span>
<!--              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Sip')" v-model="site.cloned.sip">-->
              <SimpleSearchableDropdown
                  :options="getAllAssignableSipNumbersPerSite(site)"
                  :default-selected="site.sip ? {id: site.sip, name: site.sip} : null"
                  @selected="selection => handleSipNumberSelection(selection, site)"
                  :placeholder="trans('Select SIP number')"
              />
              <span class="asterisk_input_tiny" v-if="isFieldRequired('numbers', site)"></span>
            </div>

            <!--EDITED SIM-->
            <div class="gs-1 flex items-center">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.sim))">phone</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Sim') }}</span>
                </span>
                <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Sim')" v-model="site.cloned.sim">
                <span class="asterisk_input_tiny" v-if="isFieldRequired('numbers', site)"></span>
            </div>

            <!--EDITED PBX-->
            <div class="gs-1 flex items-center">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.pbx))">phone</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pbx') }}</span>
                </span>
                <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Pbx')" v-model="site.cloned.pbx">
                <span class="asterisk_input_tiny" v-if="isFieldRequired('numbers', site)"></span>
            </div>

            <!--EDITED PSTN-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.pstn))">phone</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pstn') }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Pstn')" v-model="site.cloned.pstn">
              <span class="asterisk_input_tiny" v-if="isFieldRequired('numbers', site)"></span>
            </div>

<!--            EDITING OF THESE SETTINGS IS NOW REMOVIED-->
<!--            &lt;!&ndash;EDITED ALARM 1&ndash;&gt;-->
<!--            <div class="gs-1 flex items-center">-->
<!--              <span class="icon-wrapper tt">-->
<!--                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.alarmNumber))">bell</i>-->
<!--                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>-->
<!--              </span>-->
<!--              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.alarmNumber">-->
<!--            </div>-->

<!--            &lt;!&ndash;EDITED PERIODICAL 2&ndash;&gt;-->
<!--            <div class="gs-1 flex items-center">-->
<!--              <span class="icon-wrapper tt">-->
<!--                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.periodicalNumber))">arrow_clockwise</i>-->
<!--                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>-->
<!--              </span>-->
<!--              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.periodicalNumber">-->
<!--            </div>-->

            <!--NORMAL ALARM 1 - AS EDITED -->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'alarmNumber')">bell</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span v-show="tooltips['alarmnumber_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-top bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.alarmNumber }} ({{ trans('Level') }}: <span style="color: orange;">{{ site.alarmNumberLevel }}</span>)</span>
              </span>
              <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('alarmnumber_'+site.ds_id)" @mouseleave="hideTooltip('alarmnumber_'+site.ds_id)">{{ site.alarmNumber }}</span>
            </div>

            <!--NORMAL PERIODICAL 1 - AS EDITED -->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(site, 'periodicalNumber')">arrow_clockwise</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span v-show="tooltips['periodicalnumber_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-top bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.periodicalNumber }} ({{ trans('Level') }}: <span style="color: orange;">{{ site.periodicalNumberLevel }}</span>)</span>
              </span>
              <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('periodicalnumber_'+site.ds_id)" @mouseleave="hideTooltip('periodicalnumber_'+site.ds_id)">{{ site.periodicalNumber }}</span>
            </div>
          </div>

          <!--EDITED 2ND ROW SITE RIGHT ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="gs-1 flex items-center justify-end">

              <span class="tt-vue" v-if="tooltips['deletesite_'+site.ds_id]">
                <span v-show="tooltips['deletesite_'+site.ds_id]" class="flex justify-between items-center ttt-vue-on ttt-vue ttt-vue-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2; gap: 0.2rem; padding-right: 0.6rem;">
                  <span style="cursor: default;">Confirm site delete</span>&nbsp;
                  <span @click.prevent.stop="confirmDeleteSite(site)" style="color:green; cursor: pointer;"><i class="f7-icons icon-lg">checkmark_square_fill</i></span>&nbsp;
                  <span @click.prevent.stop="cancelDeleteSite(site)" style="color:red; cursor: pointer;"><i class="f7-icons icon-lg">xmark_square_fill</i></span>
                </span>
              </span>
              <span class="tt-vue" v-if="tooltips['cannotdeletesite_'+site.ds_id]">
                <span v-show="tooltips['cannotdeletesite_'+site.ds_id]" class="flex justify-between items-center ttt-vue-on ttt-vue ttt-vue-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2; gap: 0.2rem; padding-right: 0.6rem;">
                  <span style="cursor: default;">To delete site you need to delete it's all devices first.</span>&nbsp;
                  <span @click.prevent.stop="hideTooltip('cannotdeletesite_'+site.ds_id)" style="color:green; cursor: pointer;"><i class="f7-icons icon-lg">checkmark_square_fill</i></span>
                </span>
              </span>
              <span class="icon-wrapper tt" @click.prevent.stop="site.devices.length ? showTooltip('cannotdeletesite_'+site.ds_id) : showTooltip('deletesite_'+site.ds_id)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">trash
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Delete site') }}</span>
              </span>

            </div>
          </div>

        </div>

        <!--EDITED 3RD ROW-->
        <div class="flex justify-between items-center">

          <!--EDITED 3RD ROW LEFT SITE SPACE EMPTY -->
          <div class="flex items-start elip" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">
            <!--EDITED 3RD ROW LEFT SITE SPACE EMPTY -->
            <div v-if="activeLabels" class="gs-4 flex items-start">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.labels))">rectangle_on_rectangle_angled</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Labels') }}</span>
              </span>
            <labels-selector
                v-model="site.cloned.labels"
                :label-groups="allLabelsGroups"
                :site-id="site.ds_id"
                :key="'labels-' + site.ds_id + '-' + !!site.edited"
                @change="(labels) => $set(site.cloned, 'labels', labels)"
            ></labels-selector>
            </div>

            <div v-if="!activeLabels" class="gs-4 flex items-start"></div>

            <!--EDITED LINK-->
            <div class="gs-2 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(site, isEmpty(site.cloned.ds_link))">link</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Link') }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Link')" v-model="site.cloned.ds_link">
              <span class="asterisk_input_tiny" v-if="isFieldRequired('link', site)"></span>
            </div>
          </div>

          <!--EDITED 3RD ROW RIGHT SITE SPACE EMPTY -->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%"></div>

        </div>
      </div>


      <!--Site devices list-->
      <div v-for="device in site.devices" :key="device.device_id">

        <div class="device-row-divider top-underline-light"></div>

<!--        <a :href="'/device-site/'+site.ds_id" @click.prevent>-->
<!--        <div class="site-row" style="height: 5rem; cursor: pointer;" v-if="!site.edited" @click.self.prevent="navigateToSite(site)">-->

        <!--NORMAL DEVICE ROW-->
<!--        <a :href="'/devices/'+device.device_id" @click.prevent draggable="false">-->
        <a class="selectable-link" :href="'/device-site/'+site.ds_id" draggable="false" @click.prevent>
        <div class="device-row selectable-content flex justify-between items-center" v-if="!device.edited" @click="navigateToSite(site)">

          <!--NORMAL DEVICE LEFT ICONS-->
          <div class="flex items-start" style="width: 27%">
            <!--power-->
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.device_enabled ? colors.success : colors.empty }">power</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_enabled ? trans('Enabled') : trans('Disabled') }}</span>
            </span>

            <!--gateway-->
            <span class="icon-wrapper tt" v-if="device.gateway">
              <a :href="isEmpty(device.gatewayLink) ? 'javascript:void(0)' : device.gatewayLink" :style="{ cursor: isEmpty(device.gatewayLink) ? 'default' : 'pointer' }" @click.stop>
                <i class="f7-icons icon icon-sm tts" :style="{ 'background-color': device.gatewayColor }">globe</i>
                <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                  <!-- mac address -->
                  <div class="flex justify-between" v-if="device.mac">
                    <div class="text-left" style="width: 3rem;">
                      {{ trans('Mac:') }}
                    </div>
                    <div>
                      {{ device.mac }}
                    </div>
                  </div>
                  <!-- Imei number -->
                  <div class="flex justify-between" v-if="device.imei">
                    <div class="text-left" style="width: 3rem;">
                      {{ trans('Imei:') }}
                    </div>
                    <div>
                      {{ device.imei }}
                    </div>
                  </div>
                  <!-- Gateway expiration -->
                  <div class="flex justify-between">
                    <div class="text-left" style="width: 12rem;">
                      {{ trans('Gateway expiration:') }}
                    </div>
                    <div :style="{ color: device.gatewayColor }">
                      {{ device.gatewayValidInHours }}
                    </div>
                  </div>
                </span>
              </a>
            </span>

            <!--active alarm-->
            <span class="icon-wrapper tt" v-if="device.isActiveAlarm">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.alarm }">bell</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Active Alarm') }}</span>
            </span>

            <!--periodical overdue-->
            <span class="icon-wrapper tt" v-if="device.isPeriodical">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.error }">arrow_clockwise</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                <div class="flex">
                  <div class="text-left" style="width: 16rem;">
                    {{ device.deviceType + ' ' + trans('Periodical Check') + ':' }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedPeriodicalInHours).startsWith('-') ? colors.error : 'inherit' }">
                      {{ device.expectedPeriodicalInHours }}
                    </div>
                  </div>
                </div>
              </span>
            </span>

            <!--local check overdue-->
            <span class="icon-wrapper tt" v-if="device.expectedLocalCheckState">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.warning }">stopwatch</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                <div class="flex">
                  <div class="text-left" style="width: 12rem;">
                    {{ device.deviceType + ' ' + trans('Local Check') + ':' }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedLocalCheckInHours).startsWith('-') ? colors.error : 'inherit' }">
                      {{ device.expectedLocalCheckInHours }}
                    </div>
                  </div>
                </div>
              </span>
            </span>

            <!--other alerts-->
            <span class="icon-wrapper tt" v-if="device.isMajorAlert || device.isMinorAlert">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.isMajorAlert ? colors.error : colors.warning }">exclamationmark_triangle</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                <div class="flex">
                  <div class="text-left" style="width: 12rem;">
                    {{ device.deviceType + ' ' + trans('Alerts') + ':' }}
                  </div>
                  <div class="text-left">
                    <div v-for="alert in device.majorAlerts" :style="{ color: colors.error }">{{ alert.at_desc }}</div>
                    <div v-for="alert in device.minorAlerts" :style="{ color: colors.warning }">{{ alert.at_desc }}</div>
                  </div>
                </div>
              </span>
            </span>

            <!--device comments-->
            <span class="icon-wrapper tt" v-if="device.comment">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">bubble_left</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.comment }}</span>
            </span>
          </div>

          <div class="gr-6" style="width: 150%">
            <!--NORMAL EQUIPMENT-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop @mouseenter="loadQrCodeForDevice(device)" class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">tag</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                  <div>{{ `${trans('Equipment')} (ID: ${device.device_id})` }}</div>
                  <div v-if="device.qrCodeValue" class="mt-2 pt-2 border-t border-gray-200">
                    <div class="flex flex-col items-center">
                      <div class="text-left mb-2">
                        {{ device.qrCodeFieldName }}: {{ device.qrCodeValue }}
                      </div>
                      <div v-if="device.qrCodeLoading" class="flex items-center justify-center" style="width: 120px; height: 120px;">
                        <div class="spinner-border" style="width: 2rem; height: 2rem;"></div>
                      </div>
                      <img v-else-if="device.qrCodeSvg" :src="device.qrCodeSvg" style="width: 120px; height: 120px;" />
                      <div v-else class="flex items-center justify-center text-gray-400" style="width: 120px; height: 120px;">
                        <span>Hover to load</span>
                      </div>
                    </div>
                  </div>
                </span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['equipment_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_equipment }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('equipment_'+device.device_id)" @mouseleave="hideTooltip('equipment_'+device.device_id)">{{ device.device_equipment }}</span>
            </div>

            <!--NORMAL IDENTITY/MODULE-->
            <div class="gs-1 flex items-center" v-if="isEmpty(device.mac) && isEmpty(device.imei)">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="{'background-color': (isEmpty(device.device_setidentity) && isEmpty(device.device_setmodule) && device.device_setmodule !== 0) ? 'lightblue' : 'orange' }">gear_alt</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ getIdentityModuleLabel(device) }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['identitymodule_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;" v-html="getIdentityModuleValue(device)"></span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('identitymodule_'+device.device_id)" @mouseleave="hideTooltip('identitymodule_'+device.device_id)" v-html="getIdentityModuleValue(device)"></span>
            </div>

            <!--NORMAL MAC/IMEI-->
            <div class="gs-1 flex items-center" v-else>
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">globe</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ getMacImeiLabel(device) }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['macimei_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ getMacImeiValue(device) }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('macimei_'+device.device_id)" @mouseleave="hideTooltip('macimei_'+device.device_id)">{{ getMacImeiValue(device) }}</span>
            </div>

            <!--NORMAL PIN-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="{'background-color': isEmpty(device.device_setpin) ? 'lightblue' : 'orange' }">lock</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pin') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['pin_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;" v-html="getPinValue(device)"></span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('pin_'+device.device_id)" @mouseleave="hideTooltip('pin_'+device.device_id)" v-html="getPinValue(device)"></span>
            </div>

            <!--NORMAL DEVICE TYPE + PROTOCOL-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue;">{{ device.deviceTypeIcon }}</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['devicetype_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('devicetype_'+device.device_id)" @mouseleave="hideTooltip('devicetype_'+device.device_id)">{{ device.deviceProtocol }}</span>
            </div>

            <!--NORMAL CUSTOM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.customFields[0]?.value))">{{ device.customFields[0]?.icon || 'info' }}</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFields[0]?.name || trans('Custom field')+' 1' }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['custom1_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFields[0]?.value || '' }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom1_'+device.device_id)" @mouseleave="hideTooltip('custom1_'+device.device_id)">{{ device.customFields[0]?.value || '' }}</span>
            </div>

            <!--NORMAL CUSTOM 2-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.customFields[1]?.value))">{{ device.customFields[1]?.icon || 'info' }}</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFields[1]?.name || trans('Custom field')+' 2' }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['custom2_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFields[1]?.value || '' }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom2_'+device.device_id)" @mouseleave="hideTooltip('custom2_'+device.device_id)">{{ device.customFields[1]?.value || '' }}</span>
            </div>
          </div>

          <!--RIGHT DEVICE ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="flex justify-end" v-if="!device.edited">

              <span class="icon-wrapper tt" v-if="device.actionButtons.carcall" @click.prevent.stop="makeFsCall('carcall', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.carcall === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.carcall === 'progress' ? 'black' : 'white'}">phone
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Carcall') }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.trigger" @click.prevent.stop="makeFsCall('trigger', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.trigger === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.trigger === 'progress' ? 'black' : 'white'}">bolt
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Trigger') }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.revival" @click.prevent.stop="makeFsCall('revival', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.revival === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.revival === 'progress' ? 'black' : 'white'}">arrow_uturn_left
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Revival') }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.set" @click.prevent.stop="makeFsCall('set', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.set === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.set === 'progress' ? 'black' : 'white'}">gear_alt
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.2;">{{ trans('Set') }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="!actionsForbidden.includes('openModal')" @click.prevent.stop="openDeviceCustomFields(device)" :style="{ 'cursor': 'pointer' }">
                <i class="f7-icons icon default icon-sm tts" :style="{'background-color': colors.button, 'color': 'white' }">list_bullet_below_rectangle</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Show custom fields / settings') }}</span>
              </span>

<!--              REMEMBER THAT DEVICE DETAILS ACCESS IS RESTRICTED-->
<!--              <a :href="'/devices/'+device.device_id">-->
<!--                <span class="icon-wrapper tt">-->
<!--                  <i class="f7-icons icon default icon-sm tts cursor-pointer"-->
<!--                     style="background-color: #8faadc;-->
<!--                     color: white;">arrow_up_right-->
<!--                  </i>-->
<!--                  <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"-->
<!--                        style="width: max-content; zoom: 1.2;">{{ trans('Go to Device') }}</span>-->
<!--                </span>-->
<!--              </a>-->
            </div>
          </div>

        </div>
        </a>

<!--                <a :href="'/devices/'+device.device_id" @click.prevent>-->
<!--        <div class="device-row flex justify-between items-center" v-if="!device.edited" @click="navigateToDevice(device)">-->
        <!--NORMAL 2ST DEVICE ROW-->
        <a class="selectable-link" :href="'/device-site/'+site.ds_id" draggable="false" @click.prevent>
        <div class="device-last-row selectable-content flex justify-between items-center" v-if="!device.edited && (!isEmpty(device.alarmNumber) || !isEmpty(device.periodicalNumber))" @click="navigateToSite(site)">

          <!--EMPTY-->
          <div class="flex items-start" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">
            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--NORMAL DEVICE ALARM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(device, 'alarmNumber')">bell</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['alarmnumber_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.alarmNumber }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('alarmnumber_'+device.device_id)" @mouseleave="hideTooltip('alarmnumber_'+device.device_id)">{{ device.alarmNumber }}</span>
            </div>

            <!--NORMAL DEVICE PERIODICAL 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(device, 'periodicalNumber')">arrow_clockwise</i>
                <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>
              </span>
              <span class="tt-vue">
                <span @click.prevent.stop v-show="tooltips['periodicalnumber_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.periodicalNumber }}</span>
              </span>
              <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('periodicalnumber_'+device.device_id)" @mouseleave="hideTooltip('periodicalnumber_'+device.device_id)">{{ device.periodicalNumber }}</span>
            </div>
          </div>

          <!--RIGHT DEVICE ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%"></div>
        </div>
        </a>


        <!--EDITED 1ST DEVICE ROW-->
        <div class="device-row flex justify-between items-center" v-if="device.edited">

          <!--EDITED DEVICE LEFT ICONS-->
          <div class="flex items-start elip" style="width: 27%">
             <span class="icon-wrapper tt">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.device_enabled ? colors.success : colors.empty }">power</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_enabled ? trans('Enabled') : trans('Disabled') }}</span>
            </span>
          </div>

          <div class="gr-6" style="width: 150%">

            <!--EDITED EQUIPMENT-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">tag</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Equipment') }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Equipment')" v-model="device.cloned.device_equipment">
              <span class="asterisk_input_tiny"></span>
            </div>

            <!--EDITED IDENTITY/MODULE-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">gear_alt</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                  {{ (isEmpty(device.device_setidentity) && isEmpty(device.device_setmodule) && device.device_setmodule !== 0) ? (trans('Identity') + ' / ' + trans('Module')) : trans('Identity') }}
                </span>
              </span>
              <template v-if="isEmpty(device.device_setidentity) && isEmpty(device.device_setmodule) && device.device_setmodule !== 0">
                <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Identity')" v-model="device.cloned.device_identity">&nbsp;
                <span class="asterisk_input_far" v-if="isFieldRequired('identity', site, device)"></span>
                <input class="input-edit elip cursor-default" :placeholder="trans('Module')" v-model="device.cloned.device_module">
                <span class="asterisk_input_tiny" v-if="isFieldRequired('module', site, device)"></span>
              </template>
              <template v-else>
                <template v-if="isEmpty(device.device_setidentity)">
                  <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Identity')" v-model="device.cloned.device_identity">&nbsp;
                  <span class="asterisk_input_far" v-if="isFieldRequired('identity', site, device)"></span>
                </template>
                <div v-else style="width: 100%; margin-right: 1.4rem;">
                  <span class="input-set w-full badge whitespace-nowrap items-center text-white whitespace-nowrap text-heavy text-xs justify-between">
                    <span class="w-full h-full items-center px-4 py-1 bg-orange-400">{{ device.device_setidentity }}</span>
                    <span @click.prevent.stop="confirmSetField(device, 'identity')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                      <i class="f7-icons tts cursor-pointer">checkmark_alt</i>
                    </span>
                    <span @click.prevent.stop="rejectSetField(device, 'identity')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                      <i class="f7-icons tts cursor-pointer">xmark</i>
                    </span>
                  </span>
                </div>
              </template>
            </div>

            <!--EDITED PIN-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{'background-color': 'lightblue'}">lock</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Pin') }}</span>
              </span>
              <template v-if="isEmpty(device.device_setpin)">
                <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Pin')" v-model="device.cloned.device_pin">
                <span class="asterisk_input_tiny" v-if="isFieldRequired('pin', site, device)"></span>
              </template>
              <div v-else class="mr-8" style="width: 100%;">
                <span class="input-set w-full badge whitespace-nowrap items-center text-white whitespace-nowrap text-heavy text-xs justify-between">
                  <span class="w-full h-full items-center px-4 py-1 bg-orange-400">{{ device.device_setpin }}</span>
                  <span @click.prevent.stop="confirmSetField(device, 'pin')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                    <i class="f7-icons tts cursor-pointer">checkmark_alt</i>
                  </span>
                  <span @click.prevent.stop="rejectSetField(device, 'pin')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                    <i class="f7-icons tts cursor-pointer">xmark</i>
                  </span>
                </span>
              </div>
            </div>

            <!--EDITED DEVICE TYPE + PROTOCOL -->
            <div class="gs-1 flex items-center">
              <template v-if="device.can_assign_gateway">
                  <span class="icon-wrapper tt">
                    <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">{{ device.deviceTypeIcon }}</i>
                    <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
                  </span>
                  <SimpleSearchableDropdown
                        :options="getAllAssignableGatewaysPerDevice(device)"
                        :default-selected="device.selectedGateway"
                        @selected="selection => handleGatewaySelectionPerDevice(selection, device)"
                        :placeholder="trans('Please select MAC or IMEI')">
                  </SimpleSearchableDropdown>
              </template>
              <template v-else>
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">{{ device.deviceTypeIcon }}</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
                </span>
                <span class="tt-vue">
                  <span v-show="tooltips['devicetype_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
                </span>
                <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('devicetype_'+device.device_id)" @mouseleave="hideTooltip('devicetype_'+device.device_id)">{{ device.deviceProtocol }}</span>
              </template>
            </div>

            <!--EDITED CUSTOM 1-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.clonedCustomFields[0]?.value), device.clonedCustomFields[0].isConfigured)">{{ device.clonedCustomFields[0]?.icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.clonedCustomFields[0]?.name || trans('Custom field')+' 1' }}</span>
              </span>
              <input v-if="device.clonedCustomFields[0].isConfigured" class="input-edit ml-4 elip cursor-default" :placeholder="device.clonedCustomFields[0]?.name || trans('Custom field')+' 1'" v-model="device.clonedCustomFields[0].value">
            </div>

            <!--EDITED CUSTOM 2-->
            <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.clonedCustomFields[1]?.value), device.clonedCustomFields[1].isConfigured)">{{ device.clonedCustomFields[1]?.icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.clonedCustomFields[1]?.name || trans('Custom field')+' 2' }}</span>
              </span>
              <input v-if="device.clonedCustomFields[1].isConfigured" class="input-edit ml-4 elip cursor-default" :placeholder="device.clonedCustomFields[1]?.name || trans('Custom field')+' 2'" v-model="device.clonedCustomFields[1].value">
            </div>
          </div>

          <!--EDITED RIGHT DEVICE ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="flex justify-end">

              <span class="icon-wrapper tt" @click.prevent.stop="toggleDeviceState(device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">{{ device.device_enabled ? 'stop_fill' : 'play_fill' }}
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_enabled ? trans('Disable device') : trans('Enable device') }}</span>
              </span>

              <span class="tt-vue">
                <span v-show="tooltips['deletedevice_'+device.device_id]" class="flex justify-between items-center ttt-vue-on ttt-vue ttt-vue-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2; gap: 0.2rem; padding-right: 0.6rem;">
                  <span style="cursor: default;">Confirm device delete</span>&nbsp;
                  <span @click.prevent.stop="confirmDeleteDevice(site, device)" style="color:green; cursor: pointer;"><i class="f7-icons icon-lg">checkmark_square_fill</i></span>&nbsp;
                  <span @click.prevent.stop="cancelDeleteDevice(device)" style="color:red; cursor: pointer;"><i class="f7-icons icon-lg">xmark_square_fill</i></span>
                </span>
              </span>
              <span class="icon-wrapper tt" @click.prevent.stop="showTooltip('deletedevice_'+device.device_id)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">trash
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Delete device') }}</span>
              </span>

            </div>
          </div>
        </div>


        <div class="device-last-row flex justify-between items-center" v-if="device.edited && (!isEmpty(device.device_setidentity) || !isEmpty(device.device_setmodule) || device.device_setmodule === 0 || !isEmpty(device.cloned.alarmNumber) || !isEmpty(device.cloned.periodicalNumber))">

          <!--EMPTY DEVICE EDITED 2ND ROW -->
          <div class="flex items-start elip" style="width: 27%"></div>

          <div class="gr-6" style="width: 150%">

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--EDITED IDENTITY/MODULE-->
            <div class="gs-1 flex items-center">
              <template v-if="!isEmpty(device.device_setmodule) || device.device_setmodule === 0 || !isEmpty(device.device_setidentity)">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">gear_alt</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Module') }}</span>
                </span>
                <template v-if="isEmpty(device.device_setmodule) && device.device_setmodule !== 0">
                  <input class="input-edit ml-4 elip cursor-default" :placeholder="trans('Module')" v-model="device.cloned.device_module" style="margin-right: 0.4rem;">
                  <span class="asterisk_input_very_far" v-if="isFieldRequired('module', site, device)"></span>
                </template>
                <div v-else style="width: 100%; margin-right: 1.4rem;">
                  <span class="input-set w-full badge whitespace-nowrap items-center text-white whitespace-nowrap text-heavy text-xs justify-between">
                    <span class="w-full h-full items-center px-4 py-1 bg-orange-400">{{ device.device_setmodule }}</span>
                    <span @click.prevent.stop="confirmSetField(device, 'module')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                      <i class="f7-icons tts cursor-pointer">checkmark_alt</i>
                    </span>
                    <span @click.prevent.stop="rejectSetField(device, 'module')" class="h-full cursor-pointer items-center px-2 py-1 bg-orange-400 hover:bg-orange-700 border-l border-white">
                      <i class="f7-icons tts cursor-pointer">xmark</i>
                    </span>
                  </span>
                </div>
              </template>
            </div>

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

            <!--EMPTY-->
            <div class="gs-1 flex items-center"></div>

<!--            EDITING IF THERE SETTING ARE REMOVED NOW-->
<!--            &lt;!&ndash;EDITED ALARM 1&ndash;&gt;-->
<!--            <div class="gs-1 flex items-center">-->
<!--              <template v-if="!isEmpty(device.cloned.alarmNumber) || !isEmpty(device.cloned.periodicalNumber)">-->
<!--                <span class="icon-wrapper tt">-->
<!--                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.cloned.alarmNumber))">bell</i>-->
<!--                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>-->
<!--                </span>-->
<!--                <input class="input-edit ml-4 elip cursor-default" v-model="device.cloned.alarmNumber">-->
<!--              </template>-->
<!--            </div>-->

<!--            &lt;!&ndash;EDITED PERIODICAL 2&ndash;&gt;-->
<!--            <div class="gs-1 flex items-center">-->
<!--              <template v-if="!isEmpty(device.cloned.alarmNumber) || !isEmpty(device.cloned.periodicalNumber)">-->
<!--                <span class="icon-wrapper tt" >-->
<!--                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyleEmpty(device, isEmpty(device.cloned.periodicalNumber))">arrow_clockwise</i>-->
<!--                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>-->
<!--                </span>-->
<!--                <input class="input-edit ml-4 elip cursor-default" v-model="device.cloned.periodicalNumber">-->
<!--              </template>-->
<!--            </div>-->

            <!--NORMAL DEVICE ALARM 1 - AS EDITED -->
            <div class="gs-1 flex items-center">
              <template v-if="!isEmpty(device.alarmNumber) || !isEmpty(device.periodicalNumber)">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(device, 'alarmNumber')">bell</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Alarm target 1') }}</span>
                </span>
                <span class="tt-vue">
                  <span v-show="tooltips['alarmnumber_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.alarmNumber }}</span>
                </span>
                <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('alarmnumber_'+device.device_id)" @mouseleave="hideTooltip('alarmnumber_'+device.device_id)">{{ device.alarmNumber }}</span>
              </template>
            </div>

            <!--NORMAL DEVICE PERIODICAL 1 - AS EDITED -->
            <div class="gs-1 flex items-center">
              <template v-if="!isEmpty(device.alarmNumber) || !isEmpty(device.periodicalNumber)">
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(device, 'periodicalNumber')">arrow_clockwise</i>
                  <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Periodical target 1') }}</span>
                </span>
                <span class="tt-vue">
                  <span v-show="tooltips['periodicalnumber_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.periodicalNumber }}</span>
                </span>
                <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('periodicalnumber_'+device.device_id)" @mouseleave="hideTooltip('periodicalnumber_'+device.device_id)">{{ device.periodicalNumber }}</span>
              </template>
            </div>
          </div>

          <!--EDITED RIGHT SIDE COMMENT 2ND ROW-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
            <div class="flex justify-end">
              <!--EMPTY-->
            </div>
          </div>
        </div>

      </div>


      <!--SITE COMMENTS-->
      <div v-if="!site.edited && site.showComments">

        <!--ADD NEW COMMENT-->
        <div class="flex justify-between items-center h-14">
          <div class="uppercase ml-4 mt-4 -mb-2">{{ trans('new comment') }}</div>
          <div class="mr-4">
             <span class="icon-wrapper tt" @click.prevent.stop="site.showComments = false">
                <i class="f7-icons icon default icon-sm tts cursor-pointer" style="background-color: #8faadc; color: white;">chevron_up</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Close section') }}</span>
             </span>
          </div>
        </div>

        <div class="device-row-divider top-underline-light"></div>

        <div class="flex justify-between items-start" style="min-height: 2rem; margin-block: 1.3rem;">
          <div class="flex items-start justify-start ml-4" style="width: 27%;">
            <div style="width: 100%;">
              <select v-model="site.newCommentEquipment" @change="$set(site.errors, 'newCommentEquipment', null);" :class="{ 'border border-red-500': site.errors.newCommentEquipment, 'placeholder': !site.newCommentEquipment  }" class="input-select elip cursor-default ml border-gray" style="width: 95%; border: solid 0.001rem #f7d9d9; background-color: white;">
                <option value="">{{ trans('Select equipment') }}</option>
                <option v-for="device in site.devices" :key="device.device_id" :value="device.device_id">{{ device.device_equipment }}</option>
              </select>
              <span v-if="site.errors.newCommentEquipment" class="text-red-500 block">{{ site.errors.newCommentEquipment }}</span>
            </div>
          </div>

          <div class="gr-9" style="width: 170%">
            <div class="gs-7">
              <div class="grow-textarea-wrap" :class="{ 'border border-red-500': site.errors.newCommentContent }" style="width: 99%;">
                <textarea rows="1" v-model="site.newCommentContent" @input="$set(site.errors, 'newCommentContent', null)" onInput="this.parentNode.dataset.replicatedValue = this.value" class="w-full input-textarea border-gray" :placeholder="trans('Add comment')"></textarea>
              </div>
              <span v-if="site.errors.newCommentContent" class="text-red-500 block">{{ site.errors.newCommentContent }}</span>
            </div>
            <div class="gs-2">
              <input type="text" v-model="site.newCommentLink" class="border-gray" :placeholder="trans('Add link')" style="padding: revert; height: 2.612rem; background-color: revert; padding-inline: 0.5rem; border: solid 0.001rem #f7d9d9;">
            </div>
          </div>

          <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 5rem; margin-top: 0.5rem;">
            <div class="flex justify-end">

              <span class="icon-wrapper tt" @click.prevent.stop="addComment(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer" style="background-color: #8faadc; color: white;">plus</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Add comment') }}</span>
              </span>

            </div>
          </div>
        </div>

        <!--COMMENTS LIST-->
        <div v-if="site.comments.length">
          <div class="device-row-divider top-underline-light"></div>

          <div class="flex justify-between items-center h-14">
            <div class="uppercase ml-4 mt-4 -mb-2">{{ trans('site comments') }}</div>
          </div>

          <div class="device-row-divider top-underline-light"></div>

          <div class="comments-container" :ref="'commentsContainer_' + site.ds_id">
            <template v-for="(comment, i) in site.comments">
              <div class="device-row flex justify-between items-center">

                <!--COMMENT CREATED-->
                <div class="flex items-start" style="width: 27%">
                  <span class="icon-wrapper tt">
                    <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">calendar_badge_plus</i>
                    <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Comment added') }}</span>
                  </span>
                  <span class="tt-vue">
                    <span v-show="tooltips['sitecommentadded_'+comment.dc_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ getFormattedDate(comment.dc_created) }}</span>
                  </span>
                  <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitecommentadded_'+comment.dc_id)" @mouseleave="hideTooltip('sitecommentadded_'+comment.dc_id)">{{ getFormattedDate(comment.dc_created) }}</span>
                </div>

                <div class="gr-6" style="width: 150%">

                  <!--COMMENT EQUIPMENT-->
                  <div class="gs-1 flex items-center">
                    <span class="icon-wrapper tt">
                      <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">tag</i>
                      <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Commented equipment') }}</span>
                    </span>
                    <span class="tt-vue">
                      <span v-show="tooltips['sitecommentequipment_'+comment.dc_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.devices.find(d => d.device_id === comment.dc_device_id)?.device_equipment }}</span>
                    </span>
                    <span style="white-space: nowrap;" class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitecommentequipment_'+comment.dc_id)" @mouseleave="hideTooltip('sitecommentequipment_'+comment.dc_id)">{{ site.devices.find(d => d.device_id === comment.dc_device_id)?.device_equipment }}</span>
                  </div>

                  <!--COMMENT TEXT-->
                  <div class="gs-4 flex items-center">
                    <span class="icon-wrapper tt">
                      <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">bubble_left</i>
                      <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Last comment') }}</span>
                    </span>
                    <span class="tt-vue">
                      <span v-show="tooltips['sitecomment_'+comment.dc_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ comment.dc_text }}</span>
                    </span>
                    <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitecomment_'+comment.dc_id)" @mouseleave="hideTooltip('sitecomment_'+comment.dc_id)">{{ comment.dc_text }}</span>
                  </div>

                  <!--COMMENT AUTHOR-->
                  <div class="gs-1 flex items-center">
                    <span class="icon-wrapper tt">
                      <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">person_badge_plus</i>
                      <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Comment author') }}</span>
                    </span>
                    <span class="tt-vue">
                      <span v-show="tooltips['sitecommentauthor_'+comment.dc_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ comment.author }}</span>
                    </span>
                    <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitecommentauthor_'+comment.dc_id)" @mouseleave="hideTooltip('sitecommentauthor_'+comment.dc_id)">{{ comment.author }}</span>
                  </div>

                </div>

                <!--RIGHT COMMENTS ICONS-->
                <div class="gr-1 flex flex-col items-start gap-2 mr-4" style="width: 20%">
                  <div class="gs-1 flex items-center justify-end">
                    <a :href="isEmpty(comment.dc_link) ? 'javascript:void(0)' : sanitizeLink(comment.dc_link)"
                       :style="{ cursor: isEmpty(comment.dc_link) ? 'default' : 'pointer', 'pointer-events': isEmpty(comment.dc_link) ? 'none' : 'auto' }">
                      <span class="icon-wrapper tt">
                        <i class="f7-icons icon default icon-sm tts" :style="{'background-color': (isEmpty(comment.dc_link) ? colors.empty : colors.button), 'color': 'white' }">link</i>
                        <span v-if="!isEmpty(comment.dc_link)"
                              class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                              style="width: max-content; zoom: 1.2;">{{ comment.dc_link }}</span>
                      </span>
                    </a>
                  </div>
                </div>

              </div>
              <div v-if="i < site.comments.length - 1" class="device-row-divider bottom-underline-light" ></div>
            </template>

          </div>
        </div>

      </div>
      <!--END OF COMMENTS-->


      <!--SITE HISTORY-->
      <div v-if="!site.edited && site.showHistory">
        <div class="device-row-divider top-underline-light"></div>

        <div class="flex justify-between items-center h-14">
          <div class="uppercase ml-4 mt-4 -mb-2">{{ trans('history timeline') }}</div>
          <div class="mr-4">
             <span class="icon-wrapper tt" @click.prevent.stop="site.showHistory = false">
                <i class="f7-icons icon default icon-sm tts cursor-pointer" style="background-color: #8faadc; color: white;">chevron_up</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Close section') }}</span>
             </span>
          </div>
        </div>

        <div class="device-row-divider top-underline-light"></div>

        <div class="flex justify-between items-center" :style="{ 'height': (150 + (site.devices.length * 50)) + 'px' }">

          <!--SITE AND EQUIPMENTS-->
          <div class="flex flex-col items-start" style="width: 11%; align-self: start; margin-top: 3.8rem; row-gap: 1.55rem;">

            <div class="flex" style="width: 100%;">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">building</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Site name') }}</span>
              </span>
              <span class="tt-vue">
                <span v-show="tooltips['sitehistory_'+site.ds_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ site.ds_name }}</span>
              </span>
              <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitehistory_'+site.ds_id)" @mouseleave="hideTooltip('sitehistory_'+site.ds_id)">{{ site.ds_name }}</span>
            </div>

            <div v-for="device in site.devices" class="flex" style="width: 100%;">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">tag</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Equipment') }}</span>
              </span>
              <span class="tt-vue">
                <span v-show="tooltips['devicehistory_'+device.device_id]" class="ttt-vue-on ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_equipment }}</span>
              </span>
              <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('devicehistory_'+device.device_id)" @mouseleave="hideTooltip('devicehistory_'+device.device_id)">{{ device.device_equipment }}</span>
            </div>

          </div>

          <div class="flex" style="width: 78%">

            <div class="relative" style="width: 0;">
              <div class="history-edge left cursor-pointer" @click.prevent.stop="toggleRangePicker('min_site_'+site.ds_id)">
                {{ getDateForHistoryEdge(historyRangesMin['sites'][site.ds_id]) }}
              </div>

              <div class="history-edge-picker left" v-if="rangePickers['min_site_'+site.ds_id]" v-click-outside="clickOutsidePicker">
                <datepicker
                  v-model="historyRangesMin['sites'][site.ds_id]"
                  :monday-first="true"
                  :disabled-dates="{ from: historyRangesMax['sites'][site.ds_id] }"
                  @input="rangeChanged"
                  :inline="true">
                </datepicker>
              </div>
            </div>

            <div style="width: 100%; border: solid 0.001rem #f7d9d9;">
              <vue-timeline
                :data="history[site.ds_id]"
                :start="historyRangesMin['sites'][site.ds_id]"
                :end="historyRangesMax['sites'][site.ds_id]"
                :width-resizable="true"
                :height-resizable="false"
                :height="100 + (site.devices.length * 50)"
                :levels="site.devices.length + 1"
                :identifier="'timeline_'+site.ds_id"
                :ref="'timeline_'+site.ds_id"
                :translations="translations"
              >
              </vue-timeline>
            </div>

            <div class="relative" style="width: 0;">
              <div class="history-edge right cursor-pointer" @click.prevent.stop="toggleRangePicker('max_site_'+site.ds_id)">
                {{ getDateForHistoryEdge(historyRangesMax['sites'][site.ds_id]) }}
              </div>

              <div class="history-edge-picker right" v-if="rangePickers['max_site_'+site.ds_id]" v-click-outside="clickOutsidePicker">
                <datepicker
                  v-model="historyRangesMax['sites'][site.ds_id]"
                  :monday-first="true"
                  :disabled-dates="{ to: historyRangesMin['sites'][site.ds_id] }"
                  @input="rangeChanged"
                  :inline="true">
                </datepicker>
              </div>
            </div>

          </div>

          <!--RIGHT HISTORY ICONS-->
          <div class="gr-1 flex flex-col gap-2 mr-4" style="width: 11%; align-self: start; margin-top: 1.2rem;">
            <div class="gs-1 flex justify-end">
<!--               <span class="icon-wrapper tt" @click.prevent.stop="site.showHistory = false">-->
<!--                <i class="f7-icons icon default icon-sm tts cursor-pointer" style="background-color: #8faadc; color: white;">chevron_up</i>-->
<!--                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Close section') }}</span>-->
<!--              </span>-->
            </div>
          </div>

        </div>
      </div>

    </div>


    <div v-if="isEmpty(sites) && !loading" class="text-center mt-20">{{ emptyMessage }}</div>

    <div class="w-full text-center" style="margin-top: 5rem;">
      <pulse-loader :loading="loading" :color="colors.default" :size="'2rem'"></pulse-loader>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import { isEmpty, capitalizeFirstLowercaseRest, getBrowser } from "../assets/js/globalUtils"
import VueTimeline from "./third-party/timeline/components/VueTimeline.vue";
import Datepicker from 'vuejs-datepicker'
import NotificationsMixin from "./mixins/NotificationsMixin";
import SimpleSearchableDropdown from "./third-party/simple-searchable-dropdown/SimpleSearchableDropdown.vue";
import LabelsSelector from "./atoms/dropdowns/LabelsSelector.vue";
import LabelsMixin from "./mixins/LabelsMixin";

const DEVICE_TYPE_ORDER = ['GATEWAY', 'TELEALARM', 'INTERCOM'];

export default {

  components: {
    PulseLoader,
    VueTimeline,
    Datepicker,
    SimpleSearchableDropdown,
    LabelsSelector,
  },

  mixins: [NotificationsMixin, LabelsMixin],

  directives: {
    'click-outside': {
      bind(el, binding, vnode) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            vnode.context[binding.expression](event);
          }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
      },
      unbind(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  },

  data() {
    return {
      sites: null,
      equipmentFieldsConfig: null,
      customFieldsConfig: null,
      translations: {},
      loading: false,
      cancelTokenSource: null,
      paginationData: {
        current: 1,
        total: 1
      },
      scrolledDownActive: false,
      alertPriority: [
        'active_alarm',
        'periodical_check_overdue',
        'local_check_overdue',
        'major_alerts',
        'minor_alerts',
      ],
      tooltips: {},
      colors: {
        alarm: 'yellow',
        error: 'lightcoral',
        warning: 'orange',
        success: 'lightgreen',
        default: 'lightblue',
        empty: 'lightgray',
        button: '#8faadc'
      },
      accountId: null,
      settings: [],
      actionInProgress: false,
      countries: [],
      required: {},
      assignableGateways: [],
      assignableSipNumbers: [],

      history: {},
      rangePickers: {},
      historyRangesMin: { sites: {}, devices: {} },
      historyRangesMax: { sites: {}, devices: {} },
      activePicker: null,
      isOpeningPicker: false,

      userHasPhone: null,
      activeLabels: null,
      currentFilters: null,
      initialFetchPromise: null,
      // --- debounce helpers for filtersChanged listener ---
      pendingFiltersTimeout: null,
      filtersBaseDelay: 700,
      filtersCurrentDelay: 700,
      filtersMaxDelay: 1500,
      lastFiltersHandledTime: 0,
      lastFiltersState: '',
    }
  },

  props: {
    emptyMessage: {
      type: String,
      required: false,
      default: 'No results found'
    },
    actionsForbidden: {
      type: Array,
      required: false,
      default: () => []
    },
  },

  methods: {
    log(msg){
      window.console.log(msg)
      return true
    },

    isEmpty(value) {
      return isEmpty(value)
    },

    trans(key) {
      return this.translations?.[key] || key;
    },

    // BELOW methods are for sip-user dropdown
    getGatewayAsOption(gateway) {
        if (!gateway) return {};
        return {
            name: gateway.dg_mac
                ? `${gateway.dg_mac} (mac)`
                : `${gateway.dg_imei} (imei)`,
            id: gateway.dg_id
        };
    },

    getAllAssignableGatewaysPerDevice(device) {
        return device.gateway
            ? [...this.assignableGateways, this.getGatewayAsOption(device.gateway)]
            : this.assignableGateways;
    },

    handleGatewaySelectionPerDevice(selection, device) {
        device.selectedGateway = selection;
        device.clonedGatewayId = selection?.id || null;
    },
    // ABOVE methods are for sip-user dropdown

    handleSipNumberSelection(selection, site) {
        if (!site.edited) return;

        site.selectedSipNumber = selection;
        site.cloned.sip = selection?.name || '';
    },

    getAllAssignableSipNumbersPerSite(site) {
        let options = [];

        if (site.sip) {
            options.push({
                id: site.sip,
                name: site.sip
            });
        }

        this.assignableSipNumbers.forEach(number => {
            if (!options.find(opt => opt.name === number.name)) {
                options.push(number);
            }
        });

        return options;
    },

    async initialFetch(siteId) {
      try {
        this.userHasPhone = document.querySelector("meta[name='has-phone']")?.getAttribute('content')
        this.activeLabels = document.querySelector("meta[name='active-labels']")?.getAttribute('content')
        let accountId = document.querySelector("meta[name='account-id']")?.getAttribute('content')
        if (!isEmpty(accountId)) {
          this.accountId = Number(accountId)
        } else {
          throw new Error('Account id is empty')
        }

        let [customFieldsConfigRes, settingsRes, countriesRes, requiredRes, labelsRes, translationsRes, gatewaysRes, sipRes] = await Promise.all([
          axios.get('/data/cfg'),
          axios.get('/data/settings'),
          axios.get('/data/countries'),
          axios.get('/data/required'),
          axios.get('/data/labels'),
          axios.get('/data/translations'),
          axios.get('/data/assignableGateways'),
          axios.get('/data/assignableSipNumbers'),
        ]);

        this.equipmentFieldsConfig = customFieldsConfigRes.data.filter(config => config.equipment)
        this.customFieldsConfig = customFieldsConfigRes.data
        this.settings = settingsRes.data
        this.countries = Object.entries(countriesRes.data).sort(([, countryA], [, countryB]) => countryA.localeCompare(countryB))
        this.required = requiredRes.data
        this.allLabelsGroups = labelsRes?.data ?? []
        this.translations = translationsRes.data
        this.assignableGateways = gatewaysRes.data
        this.assignableSipNumbers = sipRes.data


      } catch (error) {
        console.error('Error fetching data:', error);
        // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
        window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Error occurred on update') }}));
        this.loading = false
      }
    },

    loadSites(filters = null) {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('New request initiated');
        this.loading = false;
      }

      this.cancelTokenSource = axios.CancelToken.source();
      this.loading = true;


      if (this.paginationData.current <= 1) {
        window.dispatchEvent(new CustomEvent('total_count_load'));
      }

      const url = '/equipment/sites' + ('?page=' + this.paginationData.current);
      const axiosConfig = {
        cancelToken: this.cancelTokenSource.token
      };

      let request;
      if (filters) {
        request = axios.post(url, { filters: filters }, axiosConfig);
      } else {
        request = axios.get(url, axiosConfig);
      }

      request
        .then(response => {
          window.dispatchEvent(new CustomEvent('total_count_updated', { detail: { total: response.data.total }} ));

          let sitesRes = response.data.data
          sitesRes.forEach(site => {
            this.unpackSiteData(site)
          });

          if (this.paginationData.current > 1) {
            this.sites.push(...sitesRes)
          } else {
            this.sites = sitesRes
          }

          this.paginationData.total = response.data.last_page
          this.scrolledDownActive = true

          this.loading = false
        })
        .catch(error => {
          if (!axios.isCancel(error)) {
            window.dispatchEvent(new CustomEvent('notifyerror', { detail: { message: this.trans('Error occurred on update') }}));
            this.loading = false;
          }
        });
    },

    unpackSiteData(site) {
      this.unpackPhoneNumbers(site)
      this.unpackAddress(site)
      this.unpackProtocol(site)
      this.unpackSiteCustomFields(site)
      this.unpackSettingsData(site)
      this.unpackDevicesData(site)
      site.edited = false
      site.showComments = false
      site.showHistory = false
      site.newCommentEquipment = ''
      site.newCommentContent = ''
      site.newCommentLink = ''
      site.errors = {}

      if (this.activeLabels) {
        this.initSiteLabels(site.ds_id, site.labels)
      }
    },

    unpackPhoneNumbers(site) {
      // site.pstn = site.pstn?.number_value ?? ''
      // site.sim = site.sim?.number_value ?? ''
      // site.sip = site.sip?.number_value ?? ''
      // site.pbx = site.pbx?.number_value ?? ''

      site.pstn = site.numbers?.find(number => number.number_type.nt_type === 'PSTN')?.number_value ?? ''
      site.sim = site.numbers?.find(number => number.number_type.nt_type === 'SIM')?.number_value ?? ''
      site.sip = site.numbers?.find(number => number.number_type.nt_type === 'SIP')?.number_value ?? ''
      site.pbx = site.numbers?.find(number => number.number_type.nt_type === 'PBX')?.number_value ?? ''

      site.phoneNumber = site.sip || site.sim || site.pbx || site.pstn || ''
      site.numbers = {
        sip: site.sip ?? '',
        sim: site.sim ?? '',
        pbx: site.pbx ?? '',
        pstn: site.pstn ?? ''
      }
    },

    unpackAddress(site) {
      site.addressOneLine = site.address?.in_one_line || ''
    },

    unpackProtocol(site) {
      site.siteProtocol = site.module?.module_desc || site.module?.module_name || ''
    },

    unpackSiteCustomFields(site) {
      site.customFields = []
      this.equipmentFieldsConfig.forEach(config => {
        if (config.is_device) return
        site.customFields.push({
          icon: config.icon ?? 'info',
          name: config.cfc_name ?? 'Custom field',
          value: site.custom_fields.find(custom => custom.cfv_cfc_id === config.cfc_id)?.cfv_value ?? '',
        })
      })
    },

    unpackSettingsData(site) {
      site.devices.forEach(device => {
        device.alarmNumber = this.settings['device.alarm1.number']?.device_settings?.[device.device_id] ?? ''
        device.periodicalNumber = this.settings['device.periodical1.number']?.device_settings?.[device.device_id] ?? ''
      })
      site.alarmNumber = this.settings['device.alarm1.number']?.ds_settings?.[site.ds_id] ??
                    this.settings['device.alarm1.number']?.label_settings?.[site.ds_id] ??
                    this.settings['device.alarm1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ??
                    this.settings['device.alarm1.number']?.mod_settings?.[site.ds_protocol_id] ??
                    this.settings['device.alarm1.number']?.acc_settings?.[this.accountId] ??
                    this.settings['device.alarm1.number']?.settings ?? ''

      site.alarmNumberLevel = this.settings['device.alarm1.number']?.ds_settings?.[site.ds_id] ? this.trans('Site settings') :
                    this.settings['device.alarm1.number']?.label_settings?.[site.ds_id] ? (this.trans('Label settings') + ` (${this.settings['device.alarm1.number']?.label_sources?.[site.ds_id] || 'Label'})`) :
                    this.settings['device.alarm1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ? this.trans('Account module settings') :
                    this.settings['device.alarm1.number']?.mod_settings?.[site.ds_protocol_id] ? this.trans('Module settings') :
                    this.settings['device.alarm1.number']?.acc_settings?.[this.accountId] ? this.trans('Account settings') :
                    this.settings['device.alarm1.number']?.settings ? this.trans('Root settings') : ''

      site.periodicalNumber = this.settings['device.periodical1.number']?.ds_settings?.[site.ds_id] ??
                    this.settings['device.periodical1.number']?.label_settings?.[site.ds_id] ??
                    this.settings['device.periodical1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ??
                    this.settings['device.periodical1.number']?.mod_settings?.[site.ds_protocol_id] ??
                    this.settings['device.periodical1.number']?.acc_settings?.[this.accountId] ??
                    this.settings['device.periodical1.number']?.settings ?? ''

      site.periodicalNumberLevel = this.settings['device.periodical1.number']?.ds_settings?.[site.ds_id] ? this.trans('Site settings') :
                    this.settings['device.periodical1.number']?.label_settings?.[site.ds_id] ? (this.trans('Label settings') + ` (${this.settings['device.periodical1.number']?.label_sources?.[site.ds_id] || 'Label'})`) :
                    this.settings['device.periodical1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ? this.trans('Account module settings') :
                    this.settings['device.periodical1.number']?.mod_settings?.[site.ds_protocol_id] ? this.trans('Module settings') :
                    this.settings['device.periodical1.number']?.acc_settings?.[this.accountId] ? this.trans('Account settings') :
                    this.settings['device.periodical1.number']?.settings ? this.trans('Root settings') : ''

      site.cliNumber = this.settings['call.alarm.route1.cli.number']?.ds_settings?.[site.ds_id] ??
                    this.settings['call.alarm.route1.cli.number']?.label_settings?.[site.ds_id] ??
                    this.settings['call.alarm.route1.cli.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ??
                    this.settings['call.alarm.route1.cli.number']?.mod_settings?.[site.ds_protocol_id] ??
                    this.settings['call.alarm.route1.cli.number']?.acc_settings?.[this.accountId] ??
                    this.settings['call.alarm.route1.cli.number']?.settings ?? ''

      site.cliNumberLevel = this.settings['call.alarm.route1.cli.number']?.ds_settings?.[site.ds_id] ? this.trans('Site settings') :
                    this.settings['call.alarm.route1.cli.number']?.label_settings?.[site.ds_id] ? (this.trans('Label settings') + ` (${this.settings['call.alarm.route1.cli.number']?.label_sources?.[site.ds_id] || 'Label'})`) :
                    this.settings['call.alarm.route1.cli.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ? this.trans('Account module settings') :
                    this.settings['call.alarm.route1.cli.number']?.mod_settings?.[site.ds_protocol_id] ? this.trans('Module settings') :
                    this.settings['call.alarm.route1.cli.number']?.acc_settings?.[this.accountId] ? this.trans('Account settings') :
                    this.settings['call.alarm.route1.cli.number']?.settings ? this.trans('Root settings') : ''
    },

    unpackDevicesData(site) {
      site.devices = this.sortDevicesByTypeAndEquipment(site.devices);
      site.devices.forEach(device => {
        this.unpackDeviceData(device)
      })
    },

    unpackDeviceData(device) {
      this.unpackAlertsData(device)
      this.unpackExpectedChecks(device)
      this.unpackGatewayData(device)
      this.unpackDeviceCustomFields(device)
      this.unpackOtherDeviceData(device)
      this.unpackActionButtons(device)
      device.errors = {}
    },

    sortDevicesByTypeAndEquipment(devices) {
      return devices.sort((a, b) => {
        // Primary sort: by module type
        const aTypeIndex = DEVICE_TYPE_ORDER.indexOf(a.module?.module_type?.mt_type);
        const bTypeIndex = DEVICE_TYPE_ORDER.indexOf(b.module?.module_type?.mt_type);
        const typeComparison = (aTypeIndex === -1 ? DEVICE_TYPE_ORDER.length : aTypeIndex) -
                               (bTypeIndex === -1 ? DEVICE_TYPE_ORDER.length : bTypeIndex);

        // If types are the same, secondary sort: by device_equipment
        if (typeComparison === 0) {
          return a.device_equipment?.localeCompare(b.device_equipment);
        }

        return typeComparison;
      });
    },

    unpackAlertsData(device) {
      let alertsUnique = Array.isArray(device.device_alerts_unique)
        ? device.device_alerts_unique
        : Object.values(device.device_alerts_unique || {});

      device.alertTypes = alertsUnique.map(a => a.alert_type)
      device.isAlert = device.alertTypes.length > 0

      device.activeAlarms = device.alertTypes.filter(at => at.at_type === 'ALARM')
      device.isActiveAlarm = device.activeAlarms.length > 0

      device.periodicals = device.alertTypes.filter(at => at.at_type === 'PERIODICAL')
      device.isPeriodical = device.periodicals.length > 0

      device.majorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MAJOR' && !['ALARM','PERIODICAL'].includes(at.at_type))
      device.isMajorAlert = device.majorAlerts.length > 0

      device.minorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MINOR' && !['ALARM','PERIODICAL'].includes(at.at_type))
      device.isMinorAlert = device.minorAlerts.length > 0
    },

    unpackExpectedChecks(device) {
      if (device.expected_periodical_in_hours) {
        device.expectedPeriodicalInHours = -device.expected_periodical_in_hours + ' h'
        device.expectedPeriodicalState = Math.sign(device.expected_periodical_in_hours) < 0
        device.expectedPeriodicalState && (device.expectedPeriodicalInHours = '+ ' + device.expectedPeriodicalInHours)
      } else if (device.expected_periodical_in_hours === 0) {
        device.expectedPeriodicalInHours = -device.expected_periodical_in_minutes + ' min'
        device.expectedPeriodicalState = Math.sign(device.expected_periodical_in_minutes) < 0
        device.expectedPeriodicalState && (device.expectedPeriodicalInHours = '+ ' + device.expectedPeriodicalInHours)
      } else {
        device.expectedPeriodicalInHours = this.trans('Interval not defined')
        device.expectedPeriodicalState = null
      }

      if (!isEmpty(device.expected_local_check_in_hours)) {
        device.expectedLocalCheckInHours = -device.expected_local_check_in_hours + ' h'
        device.expectedLocalCheckState = Math.sign(device.expected_local_check_in_hours) < 0
        device.expectedLocalCheckState && (device.expectedLocalCheckInHours = '+ ' + device.expectedLocalCheckInHours)
      } else {
        device.expectedLocalCheckInHours = this.trans('Interval not defined')
        device.expectedLocalCheckState = null
      }
    },

    unpackGatewayData(device) {
      if (isEmpty(device.gateway)) {
        return
      }

      if (device.gateway?.valid_in_hours) {
        device.gatewayValidInHours = -device.gateway?.valid_in_hours + ' h'
        device.gatewayValidInHoursState = Math.sign(device.gateway?.valid_in_hours) < 0
        device.gatewayValidInHoursState && (device.gatewayValidInHours = '+ ' + device.gatewayValidInHours)
      } else if (device.gateway?.valid_in_hours === 0) {
        device.gatewayValidInHours = -device.gateway?.valid_in_minutes + ' min'
        device.gatewayValidInHoursState = device.gateway?.is_valid
        device.gatewayValidInHoursState && (device.gatewayValidInHours = '+ ' + device.gatewayValidInHours)
      } else {
        device.gatewayValidInHours = this.trans('Expiration not defined')
        device.gatewayValidInHoursState = null
      }

      device.gatewayColor = device.gatewayValidInHoursState === true ? this.colors.success : (device.gatewayValidInHoursState === false ? this.colors.error : this.colors.default)

      device.mac = device.gateway?.dg_mac || ''
      device.imei = device.gateway?.dg_imei || ''

      device.gatewayLink = null
      if (device.gateway) {
        let link = '/settings/gateways?search='
        if (device.mac) {
          link = link + 'mac,' + device.mac
        } else if (device.imei) {
          link = link + 'imei,' + device.imei
        }
        device.gatewayLink = link
      }
    },

    unpackDeviceCustomFields(device) {
      device.customFields = []
      this.equipmentFieldsConfig.forEach(config => {
        if (!config.is_device) return
        device.customFields.push({
          icon: config.icon ?? 'info',
          name: config.cfc_name ?? 'Custom field',
          value: device.custom_fields.find(custom => custom.cfv_cfc_id === config.cfc_id)?.cfv_value ?? '',
        })
      })
    },

    unpackOtherDeviceData(device) {
      device.comment = device.latest_comment?.dc_text || ''
      device.deviceProtocol = device.module?.module_desc || device.module?.module_name || ''
      device.deviceType = capitalizeFirstLowercaseRest(device.module?.module_type?.mt_type) || ''
      const deviceTypeIcons = {
        'Gateway': 'dot_radiowaves_right',
        'Telealarm': 'phone_arrow_up_right',
        'Intercom': 'speaker_2'
      }
      device.deviceTypeIcon = deviceTypeIcons[device.deviceType] || 'gear_alt';
      
      this.unpackQrCodeData(device);
    },

    unpackQrCodeData(device) {
      device.qrCodeValue = null;
      device.qrCodeFieldName = null;
      device.qrCodeSvg = null;

      if (!device.custom_fields || !this.customFieldsConfig || !this.customFieldsConfig.length) {
        return;
      }

      const qrCodeConfig = this.customFieldsConfig.find(config => 
        config.cfc_is_device && config.qr_code === true
      );

      if (!qrCodeConfig) {
        return;
      }

      const qrCodeField = device.custom_fields.find(field => 
        field.cfv_cfc_id === qrCodeConfig.cfc_id && field.cfv_value
      );

      if (qrCodeField && qrCodeField.cfv_value) {
        device.qrCodeValue = qrCodeField.cfv_value;
        device.qrCodeFieldName = qrCodeConfig.cfc_name;
        device.qrCodeSvg = null; // Will be loaded on hover
        device.qrCodeLoading = false;
      }
    },

    generateQrCode(value) {
      return `/data/qr-code?value=${encodeURIComponent(value)}`;
    },

    async loadQrCodeForDevice(device) {
      if (!device.qrCodeValue || device.qrCodeSvg || device.qrCodeLoading) {
        return;
      }

      device.qrCodeLoading = true;
      try {
        device.qrCodeSvg = this.generateQrCode(device.qrCodeValue);
      } catch (error) {
        console.error('Failed to load QR code:', error);
      } finally {
        device.qrCodeLoading = false;
      }
    },

    unpackActionButtons(device) {
      device.actionButtons = {
        carcall: this.userHasPhone && device.module?.funktions?.some(obj => obj.function_call === '_carcall'),
        revival: device.module?.funktions?.some(obj => obj.function_call === '_revival'),
        set: device.module?.funktions?.some(obj => obj.function_call === '_set'),
        trigger: device.module?.funktions?.some(obj => obj.function_call === '_trigger')
      }
    },

    isFieldRequired(field, site, device) {
      let siteRequired = this.required[site.module?.module_name]?.includes(field)
      let deviceRequired = device && this.required[device.module?.module_name]?.includes(field)
      return siteRequired || deviceRequired
    },

    getMacImeiLabel(device) {
      if (device.mac && !device.imei) {
        return this.trans('MAC')
      }
      if (!device.mac && device.imei) {
        return this.trans('IMEI')
      }
      if (device.mac && device.imei) {
        return this.trans('MAC') + ' / ' + this.trans('IMEI')
      }
      return ''
    },

    getMacImeiValue(device) {
      if (device.mac && !device.imei) {
        return device.mac
      }
      if (!device.mac && device.imei) {
        return device.imei
      }
      if (device.mac && device.imei) {
        return device.mac + ' / ' + device.imei
      }
      return ''
    },

    getIdentityModuleLabel(device) {
      let isIdentity = !isEmpty(device.device_identity)
      let isIdentitySet = !isEmpty(device.device_setidentity)
      let isModule = !isEmpty(device.device_module)
      let isModuleSet = !isEmpty(device.device_setmodule) || device.device_setmodule === 0
      let isAnyIdentity = isIdentity || isIdentitySet
      let isAnyModule = isModule || isModuleSet

      if (isAnyIdentity && !isAnyModule) {
        return this.trans('Identity')
      }
      if (!isAnyIdentity && isAnyModule) {
        return this.trans('Module')
      }
      if (isAnyIdentity && isAnyModule) {
        return this.trans('Identity') + ' / ' + this.trans('Module')
      }
      return this.trans('Module')
    },

    getIdentityModuleValue(device) {
      let isIdentity = !isEmpty(device.device_identity)
      let isIdentitySet = !isEmpty(device.device_setidentity)
      let isModule = !isEmpty(device.device_module)
      let isModuleSet = !isEmpty(device.device_setmodule) || device.device_setmodule === 0
      let isAnyIdentity = isIdentity || isIdentitySet
      let isAnyModule = isModule || isModuleSet

      if (!isAnyIdentity && !isAnyModule) {
        return ''
      }

      let valueIdentity = ''
      if (isIdentity) { valueIdentity = valueIdentity + device.device_identity }
      if (isIdentitySet) {
        let valueSet = '<span class="text-orange-500">(' + device.device_setidentity + ')</span>'
        valueIdentity = valueIdentity ? valueIdentity + ' ' + valueSet : valueSet
      }

      let valueModule = ''
      if (isModule) { valueModule = valueModule + device.device_module }
      if (isModuleSet) {
        let valueSet = '<span class="text-orange-500">(' + device.device_setmodule + ')</span>'
        valueModule = valueModule ? valueModule + ' ' + valueSet : valueSet
      }

      if (isAnyIdentity && !isAnyModule) {
        return valueIdentity
      }

      if (!isAnyIdentity && isAnyModule) {
        return valueModule
      }

      return valueIdentity+' / '+valueModule
    },

    getPinValue(device) {
      let isPin = !isEmpty(device.device_pin)
      let isPinSet = !isEmpty(device.device_setpin)

      let valuePin = ''
      if (isPin) { valuePin = valuePin + device.device_pin }
      if (isPinSet) {
        let valueSet = '<span class="text-orange-500">(' + device.device_setpin + ')</span>'
        valuePin = valuePin ? valuePin + ' ' + valueSet : valueSet
      }
      return valuePin
    },

    getFormattedDate(apiDate) {
      return new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(apiDate)).replace(',', ' :');
    },

    setNextPaginationPage() {
      if (this.paginationData.current < this.paginationData.total) {
        return ++this.paginationData.current
      }
      return false
    },

    // updatedFilters() {
    //   this.sites = null;
    //   this.paginationData = { current: 1, total: 1 };
    //   this.waitForConfigs().then(() => {
    //     this.loadSites();
    //   });
    // },

    showTooltip(tooltip) {
      this.$set(this.tooltips, tooltip, true)
    },

    hideTooltip(tooltip) {
      this.$set(this.tooltips, tooltip, false)
    },

    clickOutsidePicker() {
      if (this.activePicker && !this.isOpeningPicker) {
        this.hideRangePicker(this.activePicker);
      }
    },

    toggleRangePicker(picker) {
      this.isOpeningPicker = true;
      if (this.rangePickers[picker]) {
        this.hideRangePicker(picker)
      } else {
        this.showRangePicker(picker)
      }
      setTimeout(() => {
        this.isOpeningPicker = false;
      }, 50);
    },

    showRangePicker(picker) {
      for (let key in this.rangePickers) {
        this.$set(this.rangePickers, key, false);
      }
      this.$set(this.rangePickers, picker, true)
      this.activePicker = picker;
    },

    hideRangePicker(picker) {
      this.$set(this.rangePickers, picker, false)
      if (this.activePicker === picker) {
        this.activePicker = null;
      }
    },

    getActiveRangePicker() {
      for (let key in this.rangePickers) {
        if (this.rangePickers[key]) {
          return key;
        }
      }
      return null;
    },

    getAndParseActiveRangePicker() {
      const activePicker = this.getActiveRangePicker();
      if (activePicker) {
        const [edge, target, id] = activePicker.split('_');
        return { edge, target, id };
      }
      return null;
    },

    capitalize(value) {
      return capitalizeFirstLowercaseRest(value)
    },

    didMostImportantNumberChanged(site) {
      let oldNumber = site.sip || site.sim || site.pbx || site.pstn || ''
      let newNumber = site.cloned.sip || site.cloned.sim || site.cloned.pbx || site.cloned.pstn || ''
      return oldNumber !== newNumber;
    },

    startEdit(site) {
      site.cloned = { ...site }
      site.clonedCustomFields = [];
      [0, 1].forEach(index => {
        site.clonedCustomFields.push({
          icon: site.customFields[index]?.icon ?? 'info',
          name: site.customFields[index]?.name ?? 'Custom field',
          value: site.customFields[index]?.value ?? '',
          isConfigured: !isEmpty(site.customFields[index] ?? null),
        })
      })

      site.cloned.address = {
        street: site.address?.address_value ?? '',
        zip: site.address?.location?.location_postcode ?? '',
        city: site.address?.location?.location_value ?? '',
        countryId: site.address?.location?.country?.country_id ?? ''
      }

      site.devices.forEach(device => {
        device.cloned = { ...device }
        device.clonedCustomFields = [];
        [0, 1].forEach(index => {
          device.clonedCustomFields.push({
            icon: device.customFields[index]?.icon ?? 'info',
            name: device.customFields[index]?.name ?? 'Custom field',
            value: device.customFields[index]?.value ?? '',
            isConfigured: !isEmpty(device.customFields[index] ?? null),
          })
        })
        if (device.can_assign_gateway) {
            // device.clonedGatewayId = device.clonedGatewayId ?? device.gateway?.dg_id ?? null // todo: this is how saved state could look like
            device.clonedGatewayId = device.gateway?.dg_id ?? null
            device.selectedGateway = this.getGatewayAsOption(device.gateway)
        }
        device.edited = true
      })

      site.updateCli = false
      site.edited = true
    },

    cancelEdit(site) {
        site.cloned.labels = [...(site.labels || [])];
        site.devices.forEach(device => {
            device.edited = false;
        });
        site.edited = false;
    },

    saveEdit(site) {
      if (this.didMostImportantNumberChanged(site)) {
        this.pendingSaveSite = site;
        this.openCliConfirmationModal(site);
        return;
      }

      this.proceedSave(site)
    },

    proceedSave(site) {
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}))
      axios.post('/equipment/saveSite', site)
        .then(response => {

          if (response.data.success === true) {
            this.settings = response.data.settings
            let savedSite = response.data.site

            this.unpackSiteData(savedSite)
            Object.assign(site, savedSite)
            site.edited = false
            site.devices.forEach((device) => device.edited = false)

            this.notifySuccess(this.trans('Save succeeded'))
          } else {
            this.notifyError(this.trans('Save failed'))
          }

          this.displayResponseErrors(response)
          this.displayResponseNotifications(response)
        })
        .catch(error => {
          this.notifyError(this.trans('Save failed'));
          if (error.response?.data) {
            this.displayResponseErrors(error.response)
            this.displayResponseNotifications(error.response)
          }
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ))
        })
    },

    async reloadSite(site) {
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}))
      try {
        let [siteRes, settingsRes] = await Promise.all([
          axios.post('/equipment/site', { siteId: site.ds_id }),
          axios.get('/data/settings'),
        ]);

        this.settings = settingsRes.data
        let savedSite = siteRes.data
        this.unpackSiteData(savedSite)
        Object.assign(site, savedSite);

        // window.dispatchEvent(new CustomEvent('notify', {detail: ['Reload succeeded', 'success'] } ))
      } catch (error) {
        window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Reload failed') }} ))
      }
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ))
    },

    rejectSetField(device, field) {
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}))
      axios.post('/equipment/rejectSet', { deviceId: device.device_id, field: field })
        .then(response => {
          if (response.data.success === true) {

            let newDevice = response.data.device
            device.cloned['device_'+field] = newDevice['device_'+field]
            this.unpackDeviceData(newDevice)
            Object.assign(device, newDevice);

            window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Reject succeeded'), 'success'] } ))
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: 'Reject failed'}} ))
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Reject failed') }} ))
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ))
      })
    },

    confirmSetField(device, field) {
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}))
      axios.post('/equipment/confirmSet', { deviceId: device.device_id, field: field })
        .then(response => {
          if (response.data.success === true) {

            let newDevice = response.data.device
            device.cloned['device_'+field] = newDevice['device_'+field]
            this.unpackDeviceData(newDevice)
            Object.assign(device, newDevice)

            window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Confirm succeeded'), 'success'] } ))
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: 'Confirm failed'}} ))
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Confirm failed') }} ))
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ))
      })
    },

    getSiteIdFromTimelineId(identifier) {
        const prefix = "timeline_"
        if (identifier.startsWith(prefix)) {
            const numStr = identifier.slice(prefix.length)
            return parseInt(numStr, 10)
        }
        return null;
    },

    rangeChanged(date) {
      let picker = this.getAndParseActiveRangePicker()
      let edge = picker.edge
      let target = picker.target
      let id = picker.id

      this.hideRangePicker(this.getActiveRangePicker())

      let minDate = this.historyRangesMin['sites'][id]
      let maxDate = this.historyRangesMax['sites'][id]

      this.reloadHistory(id, minDate, maxDate)
    },

    reloadHistory(siteId, start, end) {
      siteId = Number(siteId)
      let requestStart = this.getDateForHistoryRequest(start)
      let requestEnd = this.getDateForHistoryRequest(end)

      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/siteHistory', { siteId: siteId, start: requestStart, end: requestEnd })
        .then(response => {
          let historyRes = response.data
          let foundSite = this.sites.find(obj => obj.ds_id === siteId);
          let devicePosMap = this.createHistoryDevicePositionMap(foundSite)
          let historyArray = historyRes.map((ses) => this.createHistoryItem(ses, devicePosMap, siteId))

          this.$refs['timeline_'+siteId][0].initTimeline(historyArray, start, end)

          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
        })
        .catch(error => {
          // console.dir(error)
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Error occurred on update') }}));
        })
    },

    showHistory(site, start, end) {
      let today = new Date()
      let monthAgo = new Date()
      monthAgo.setDate(monthAgo.getDate() - 30);

      start = start ?? monthAgo
      end = end ?? today

      this.historyRangesMin['sites'][site.ds_id] = start
      this.historyRangesMax['sites'][site.ds_id] = end

      let requestStart = this.getDateForHistoryRequest(start)
      let requestEnd = this.getDateForHistoryRequest(end)

      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/siteHistory', { siteId: site.ds_id, start: requestStart, end: requestEnd })
        .then(response => {
          let historyRes = response.data
          let devicePosMap = this.createHistoryDevicePositionMap(site)
          let historyArray = historyRes.map((ses) => this.createHistoryItem(ses, devicePosMap, site.ds_id))

          this.history[site.ds_id] = historyArray

          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
          site.showHistory = true
        })
        .catch(error => {
          // console.dir(error)
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Error occurred on update') }}));
        })
    },

    getDateForHistoryRequest(date) {
      date = date ?? new Date();
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}.${month}.${year}`;
    },

    // todo: this beloew and above seems kinda similar - maybe its a good ide to combine them
    getDateForHistoryEdge(date) {
      const dd = String(date.getDate()).padStart(2, '0');
      const mm = String(date.getMonth() + 1).padStart(2, '0');
      const yyyy = date.getFullYear();
      return `${dd}.${mm}.${yyyy}`;
    },

    // getHistoryDateEdges(events) {
    //   const dates = events.map(item => item.start);
    //   const minDate = new Date(Math.min(...dates));
    //   const maxDate = new Date(Math.max(...dates));
    //   return {
    //     edges: {
    //       min: this.getDateForHistoryEdge(minDate),
    //       max: this.getDateForHistoryEdge(maxDate)
    //     },
    //     range: {
    //       min: minDate,
    //       max: maxDate
    //     }
    //   }
    // },

    invertObject(obj) {
      const invertedObj = {};
      for (const [key, value] of Object.entries(obj)) {
        invertedObj[value] = key;
      }
      return invertedObj;
    },

    createHistoryDevicePositionMap(site) {
      const devicePositionMap = {};
      site.devices.forEach((device, index) => {
        devicePositionMap[device.device_id] = index + 2;
      });
      return devicePositionMap;
    },

    createHistoryItem(session, devicePosMap, siteId) {
      let start = this.convertUtcStringToLocalDate(session.session_start);
      let end = session.session_end ? this.convertUtcStringToLocalDate(session.session_end) : null;

      let startRange = this.getDateForHistoryRequest(new Date(session.session_start))
      let endRange = this.getDateForHistoryRequest(new Date(session.session_end ?? session.session_start))

      return {
          ses_id: session.session_id,
          type: session.session_type?.st_type,
          color: this.mapSessionErrorsToColor(session),
          icon: this.mapSessionTypeToIcon(session.session_type?.st_type),
          start: start,
          end: end || start,
          durationCustom: this.calculateDuration(start, end),
          position: (session.session_ds_id && !session.session_device_id) ? 1 : devicePosMap[session.session_device_id],
          url: '/device-site/'+siteId+'?startHistory='+startRange+'&endHistory='+endRange+'&session='+session.session_id,

          // url: (session.session_ds_id && !session.session_device_id) ?
          //   ('/device-site/'+session.session_ds_id+'?startHistory='+startRange+'&endHistory='+endRange+'&session='+session.session_id) :
          //   ('/devices/'+session.session_device_id+'?startHistory='+startRange+'&endHistory='+endRange+'&session='+session.session_id)
      }
    },

    convertUtcStringToLocalDate(string) {
      let utcDate = new Date(string)
      let localDate = new Date(utcDate)
      localDate.setMinutes(utcDate.getMinutes() - utcDate.getTimezoneOffset())
      return localDate
    },

    calculateDuration(start, end) {
      if (!end) {
        return "In progress";
      }
      let durationMillis = end - start;
      let hours = Math.floor(durationMillis / (1000 * 60 * 60));
      let minutes = Math.floor((durationMillis % (1000 * 60 * 60)) / (1000 * 60));
      let seconds = Math.floor((durationMillis % (1000 * 60)) / 1000);
      let durationParts = [];
      if (hours > 0) {
        durationParts.push(`${hours}h`);
      }
      if (minutes > 0 || (hours > 0 && seconds > 0)) {
        durationParts.push(`${minutes}m`);
      }
      if (seconds > 0 || (hours === 0 && minutes === 0)) {
        durationParts.push(`${seconds}s`);
      }
      return durationParts.join(' ');
    },

    mapSessionTypeToIcon(type) {
      return {
        SYSTEM: 'floppy_disk',
        CALL: 'phone',
        CARCALL: 'phone',
        ALARM: 'bell',
        PERIODICAL: 'arrow_clockwise',
        MONITOR: 'eye',
        SET: 'gear_alt',
        REVIVAL: 'arrow_uturn_left',
        AGENT: 'headphones',
        PARROT: 'speaker',
        TRIGGER: 'bolt',
        TECH: 'wrench',
        API: 'cloud_upload',
      }[type] || 'question'
    },

    mapSessionErrorsToColor(session) {
      switch (true) {
        case session.session_critical > 0 || session.session_errors > 0:
          return 'lightcoral'
        case session.sesssion_warnings > 0:
          return 'orange'
        default:
          return '#65b28b'
      }
    },

    cancelDeleteSite(site) {
      this.$set(this.tooltips, 'deletesite_'+site.ds_id, false)
    },

    confirmDeleteSite(deleteSite) {
      this.$set(this.tooltips, 'deletesite_'+deleteSite.ds_id, false)
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/deleteSite', { siteId: deleteSite.ds_id })
        .then(response => {
          if (response.data.success === true) {

            this.sites.forEach(site => {
              if (site === deleteSite) {
                this.sites.splice(this.sites.indexOf(deleteSite), 1);
              }
            })

            window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Site deleted'), 'success'] } ));
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Site delete failed') }} ));
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Site delete failed') }} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
      })
    },

    cancelDeleteDevice(device) {
      this.$set(this.tooltips, 'deletedevice_'+device.device_id, false)
    },

    confirmDeleteDevice(site, deleteDevice) {
      this.$set(this.tooltips, 'deletedevice_'+deleteDevice.device_id, false)
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/deleteDevice', { deviceId: deleteDevice.device_id })
        .then(response => {
          if (response.data.success === true) {

            site.devices.forEach(device => {
              if (device === deleteDevice) {
                site.devices.splice(site.devices.indexOf(deleteDevice), 1);
              }
            })

            window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Device deleted'), 'success'] } ));
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Device delete failed') }} ));
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Device delete failed') }} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
      })
    },

    makeFsCall(action, device) {
      this.actionInProgress = true
      device.actionButtons[action] = 'progress'

      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/fsCall', { action: action, deviceId: device.device_id })
        .then(response => {
          if (response.data === 'success') {
            window.dispatchEvent(new CustomEvent('notify', {detail: [this.capitalize(action)+' '+this.trans('action succeeded'), 'success']} ));
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.capitalize(action)+' '+this.trans('action failed') }} ));
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.capitalize(action)+' '+this.trans('action failed') }} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
          this.actionInProgress = false
          device.actionButtons[action] = true
      })
    },

    toggleDeviceState(device) {
      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('/equipment/toggleDeviceState', { deviceId: device.device_id })
        .then(response => {
          if (response.data.success === true && response.data.device) {
            device.device_enabled = response.data.device.device_enabled
            window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Device') + ' ' + (device.device_enabled ? this.trans('enabled') : this.trans('disabled')), 'success'] } ));
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: { message: this.trans('Device') + ' ' + (device.device_enabled ? this.trans('disabling') : this.trans('enabling')) + ' ' + this.trans('failed') }} ));
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Device') + ' ' + (device.device_enabled ? this.trans('disabling') : this.trans('enabling')) + ' ' + this.trans('failed') }} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
      })
    },

    getIconStyle(entity, field) {
      let empty = isEmpty(entity[field])
      let edited = entity.edited ?? false
      let transparent = empty && !edited

      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'color': !empty ? 'black' : 'white', 'opacity': transparent ? 0.5 : 1 }
      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'opacity': transparent ? 0.5 : 1 }
      // return { 'background-color': this.colors.default, 'color': !empty ? 'black' : 'white', 'opacity': transparent ? 0.5 : 1 }
      return { 'background-color': this.colors.default, 'opacity': transparent ? 0.5 : 1 }
    },

    getIconStyleEmpty(entity, empty, isConfigured = true) {
      let edited = entity.edited ?? false
      let transparent = (empty && !edited) || !isConfigured

      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'color': !empty ? 'black' : 'white', 'opacity': transparent ? 0.5 : 1 }
      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'opacity': transparent ? 0.5 : 1 }
      // return { 'background-color': this.colors.default, 'color': !empty ? 'black' : 'white', 'opacity': transparent ? 0.5 : 1 }
      return { 'background-color': this.colors.default, 'opacity': transparent ? 0.5 : 1 }
    },

    adjustTextarea(event) {
      const textarea = event.target;
      // textarea.style.height = textarea.scrollHeight + 'px';
      textarea.style.height = textarea.scrollHeight + 'px';
    },

    showComments(site) {
      site.showComments = true
      this.$nextTick(() => {
        const refName = 'commentsContainer_' + site.ds_id;
        const container = this.$refs[refName];
        if (container && container[0]) {
          container[0].scrollTop = 0;
        }
      });
    },

    addComment(site) {
      let holdOn = false
      if (isEmpty(site.newCommentContent)) {
        this.$set(site.errors, 'newCommentContent', this.trans('Field is required'))
        holdOn = true
      }
      if (isEmpty(site.newCommentEquipment)) {
        this.$set(site.errors, 'newCommentEquipment', this.trans('Field is required'))
        holdOn = true
      }
      if (holdOn) {
        return
      }

      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}))
      axios.post('/equipment/addComment', { deviceId: site.newCommentEquipment, newComment: site.newCommentContent, link: site.newCommentLink })
        .then(response => {
          if (response.data.success === true && response.data.comments) {
            // reset form
            site.newCommentContent = ''
            site.newCommentEquipment = ''
            site.newCommentLink = ''
            this.$set(site.errors, 'newCommentContent', null)
            this.$set(site.errors, 'newCommentEquipment', null)
            // rerender comments
            site.comments = response.data.comments
            window.dispatchEvent(new CustomEvent('notify', { detail: [this.trans('Comment saved'), 'success'] } ))
            this.showComments(site)
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', { detail: {message: this.trans('Adding comment error') }} ))
            if (response.data.errors?.newComment) {
              this.$set(site.errors, 'newCommentContent', response.data.errors.newComment)
            }
            if (response.data.errors?.deviceId) {
              this.$set(site.errors, 'newCommentEquipment', response.data.errors.deviceId)
            }
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', { detail: {message: this.trans('Adding comment error') }} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
      })
    },

    sanitizeLink(link) {
      link = link.replace(/(^\w+:|^)\/\//, '');
      return '//' + link;
    },

    openSiteCustomFields(site) {
      window.dispatchEvent(new CustomEvent('openModal_siteSettingsCustomFields', { detail: { siteId: site.ds_id, siteName: site.ds_name || this.trans('not entered') } }))
    },

    openDeviceCustomFields(device) {
      window.dispatchEvent(new CustomEvent('openModal_deviceSettingsCustomFields', { detail: { deviceId: device.device_id, deviceEquipment: device.device_equipment || this.trans('not entered') } }))
    },

    openCliConfirmationModal(site) {
      let newNumber = site.cloned.sip || site.cloned.sim || site.cloned.pbx || site.cloned.pstn || ''
      window.dispatchEvent(new CustomEvent('openModal_cliConfirmationModal', { detail: { newNumber: newNumber, cliNumber: site.cliNumber || this.trans('not entered'), cliNumberLevel: site.cliNumberLevel } }))
    },

    isTextSelected() {
      const selection = window.getSelection();
      return selection && selection.toString().trim().length > 0;
    },

    navigateToSite(site) {
      if (this.isTextSelected()) {
        return;
      }
      window.location.href = '/device-site/' + site.ds_id;
    },

    // navigateToDevice(device) {
    //   window.location.href = '/devices/' + device.device_id;
    // },

    handleFiltersChanged(event) {
      const NOW = Date.now();
      const nextState = JSON.stringify({
        filters: event.detail.filters,
        searchTabs: event.detail.searchTabs,
      });

      console.log('LISTA RECEIVED EVENT - at ' + Date.now() + ' | next state:' + nextState)

      if (nextState === this.lastFiltersState) {
        return;
      }

      const processEvent = () => {
        this.currentFilters = event.detail.filters;
        this.sites = null;
        this.paginationData = { current: 1, total: 1 };

        this.lastFiltersHandledTime = Date.now();
        this.lastFiltersState = nextState;

        this.waitForConfigs().then(() => {
          this.loadSites(this.currentFilters);
        });

        this.filtersCurrentDelay = this.filtersBaseDelay; // reset delay
      };

      const timeSinceLast = NOW - this.lastFiltersHandledTime;

      if (timeSinceLast > this.filtersCurrentDelay && !this.pendingFiltersTimeout) {
        processEvent();
      } else {
        if (this.pendingFiltersTimeout) {
          clearTimeout(this.pendingFiltersTimeout);
        }
        this.pendingFiltersTimeout = setTimeout(() => {
          processEvent();
          this.pendingFiltersTimeout = null;
        }, this.filtersCurrentDelay);

        this.filtersCurrentDelay = Math.min(Math.round(this.filtersCurrentDelay * 1.2), this.filtersMaxDelay);
      }
    },

    async waitForConfigs() {
      if (this.initialFetchPromise) {
        return this.initialFetchPromise;
      }
      this.initialFetchPromise = this.initialFetch();
      return this.initialFetchPromise;
    },
  },

  created() {
    this.loading = true;
    // window.addEventListener('updatedFilters', this.updatedFilters); // livewire
    window.addEventListener('filtersChanged', this.handleFiltersChanged); // vue
    window.dispatchEvent(new CustomEvent('filtersPing'));
    console.log('LISTA WYSLALA PING at ' + Date.now())

    window.onscroll = (() => {
      if (this.scrolledDownActive && (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 10)) {
        this.scrolledDownActive = false
        if (this.setNextPaginationPage()) {
          this.loadSites(this.currentFilters);
        }
      }
    }).bind(this);

    this.waitForConfigs()

    const self = this;
    window.addEventListener('siteUpdated', (event) => {
      const siteId = event.detail.siteId;
      let site = self.sites.find(s => s.ds_id === siteId)
      if (site) {
        self.reloadSite(site);
      }
    })

    window.addEventListener('cliModalResponse', (event) => {
      if (this.pendingSaveSite) {
          this.pendingSaveSite.updateCli = event.detail.updateCli
          this.proceedSave(this.pendingSaveSite);
      }
    })

  },

}
</script>

<style lang="scss" scoped>

@import "resources/assets/sass/components-new/variables";

.site-box {
  width: 100%;
  background-color: #fafafc;
  border: solid 1px #eaeaea;
  margin-block: 2.9rem;
  border-radius: 0.1rem;
  padding-top: 1.2rem;
}

.site-row {
  margin-bottom: 0.5rem;
}

.device-row {
  height: 4.5rem;
}

.grow-row {
  min-height: 4.5rem;
}

//.device-history-row {
//  height: 9rem;
//}

.device-last-row {
  margin-bottom: 1.3rem;
}

.device-row-divider {
  margin: auto;
  width: 98%;
}

.icon-wrapper {
  margin-left: 1rem;
}

.icon {
  border: solid 2px $darken4;
  border-radius: 0.2rem;
  width: 1.6rem;
  height: 1.6rem;
  display: flex;
  justify-content: center;
  align-items: center;

  &.default {
    background-color: lightblue;
  }
}

.icon-sm {
  font-size: 1.1rem;
}

.icon-md {
  font-size: 1.3rem;
}

.icon-lg {
  font-size: 1.6rem;
}

.text-stroked {
  text-shadow: #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px;
}

.text-bold {
  font-weight: bold;
}

// todo: this should be part of a mixin - look for another usages
.input-edit {
  border-style: solid;
  border-width: 1px;
  border-color: #808080a2;
  width: 100%;
  outline:none;
  padding-left: 0.3rem;
  border-radius: 0.13rem;
}

.input-set {
  border-style: solid;
  border-width: 1px;
  border-color: #808080a2;
  width: 100%;
  outline:none;
  border-radius: 0.13rem;
}

.select-edit {
  font-size: 0.8rem;
  line-height: 1.7;
  padding: 0;
  //width: 257%;
  width: 150%;
  padding-left: 0.3rem;
}

.input-select.placeholder {
  color: #64748b; /* Placeholder color */
}
.input-select.placeholder {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-color: transparent;
  background-repeat: no-repeat;
  background-position: right 10px center;
}
.input-select:not(.placeholder) {
  color: black;
}
.input-select:focus {
  color: black;
}
.input-select option {
  color: black;
}
.input-select option:first-child {
  color: #64748b;
}
.input-select:focus.placeholder {
  color: #64748b;
}


.input-textarea {
  resize: none;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  //overflow: hidden; // not needed

  &:focus {
    border: solid 0.001rem #f7d9d9;
  }
  //overflow: auto;
  //outline: none;

  //line-height: 0.8rem !important;
  //min-height: 30px;
  //height: auto;
}

.asterisk_input_very_far::after {
  content: "*";
  color: #e32;
  position: absolute;
  margin: -16px 0px 0px -20px;
  font-size: 1.3rem;
}

.asterisk_input_far::after {
  content: "*";
  color: #e32;
  position: absolute;
  margin: -16px 0px 0px -18px;
  font-size: 1.3rem;
}

.asterisk_input_mid::after {
  content: "*";
  color: #e32;
  position: absolute;
  margin: -16px 0px 0px -15px;
  font-size: 1.3rem;
}

.asterisk_input_tiny::after {
  content: "*";
  color: #e32;
  position: absolute;
  margin: -16px 0px 0px -14px;
  font-size: 1.3rem;
}

.history-edge {
  position: absolute;
  background-color: white;
  border: solid 3px #5b83ca;
  border-radius: 99px;
  padding-inline: 5px;
  font-size: 0.9rem;

  &.left {
    top: -0.5rem;
    left: -3rem;
  }

  &.right {
    top: -0.5rem;
    right: -3rem;
  }
}

.history-edge-picker {
  position: absolute;
  z-index: 500;

  &.left {
    top: 1rem;
    left: 3rem;
  }

  &.right {
    top: 1rem;
    right: 3rem;
  }
}

.comments-container {
  max-height: 20rem;
  overflow-y: auto;
  overflow-x: hidden;
  margin-block: 1rem;
}


.grow-textarea-wrap {
  display: grid;
  width: 100%;
}
.grow-textarea-wrap::after {
  content: attr(data-replicated-value) " ";
  white-space: pre-wrap;
  visibility: hidden;
}
.grow-textarea-wrap > textarea {
  resize: none;
  overflow: hidden;
}
.grow-textarea-wrap > textarea,
.grow-textarea-wrap::after {
  padding: 0.5rem;
  font: inherit;
  grid-area: 1 / 1 / 2 / 2;
}

.selectable-link {
    //display: block;
    //text-decoration: none;
    //color: inherit;
    //cursor: text;
    //cursor: pointer;

    -webkit-user-select: text;
    -webkit-select: text;
    -moz-user-select: text;
    -moz-select: text;
    -ms-user-select: text;
    -ms-select: text;
    user-select: text;
    select: text;
}

.selectable-link:hover {
    background-color: transparent;
}

.selectable-content {
    pointer-events: auto;
    user-select: text;
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
}

.selectable-content::selection {
    background-color: highlight;
    color: highlighttext;
}

.link-behavior {
    cursor: pointer;
}

.test {}
</style>
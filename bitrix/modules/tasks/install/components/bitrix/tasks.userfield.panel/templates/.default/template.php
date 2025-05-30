<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Helper\RestrictionUrl;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit;

$APPLICATION->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");
\Bitrix\Main\UI\Extension::load(['uf']);
?>

<?$arResult['HELPER']->displayFatals();?>
<?if(!$arResult['HELPER']->checkHasFatals()):?>
	<?$arResult['HELPER']->displayWarnings();?>

	<?$canUse = $arResult['TEMPLATE_DATA']['CAN_USE'];?>

	<div id="<?=$arResult['HELPER']->getScopeId()?>" class="tasks">

		<div class="tasks-uf-panel">

			<?if($canUse):?>

				<a href="javascript:void(0);" class="js-id-uf-panel-action tasks-btn-customize tasks-uf-panel-settings"></a>

				<?ob_start();?>
				<div class="js-id-item-set-item js-id-item-set-item-{{VALUE}} tasks-uf-panel-row tasks {{DEFACEABLE}} {{REQUIRED}} {{EDIT}} {{INVISIBLE}}" data-type="{{USER_TYPE_ID}}" data-multiple="{{DISPLAY_MULTIPLE}}" data-item-value="{{VALUE}}">
					<div class="tasks-uf-panel-row-geometry">
						<div class="js-id-uf-panel-item-drag tasks-btn-drag"></div>
						<div class="tasks-uf-panel-row-title">
							<div class="tasks-uf-panel-row-title-text">
								<span class="tasks-uf-panel-row-title-red-star tasks-red">*</span>
								<span class="js-id-item-set-item-label">{{DISPLAY}}</span>
							</div>
							<div class="tasks-uf-panel-row-title-edit">
								<input class="js-id-item-set-item-label-edit js-id-uf-panel-item-label-edit" type="text" value="" maxlength="255" />
							</div>
						</div>
						<div class="tasks-uf-panel-row-data">
							<div class="tasks-uf-panel-row-data-value">
								<div class="js-id-item-set-item-field-html">
									{{{FIELD_HTML}}}
								</div>
								<div class="tasks-uf-panel-row-data-value-overlay"></div>
							</div>
							<div class="js-id-item-set-item-form-place tasks-uf-panel-row-data-form-place invisible">
								<div class="js-id-item-set-item-form tasks-uf-panel-form">
									<div class="tasks-uf-panel-form-flags">
										<label title="<?=Loc::getMessage('TASKS_TUFP_FIELD_MULTIPLE_HINT')?>"><input class="js-id-item-set-item-multiple-edit" type="checkbox" /><?=Loc::getMessage('TASKS_TUFP_FIELD_MULTIPLE')?></label>
										<?$createMandatory = $arResult['COMPONENT_DATA']['RESTRICTION']['CREATE_MANDATORY'];?>
										<span <?if(!$createMandatory):?>class="tasks-btn-restricted" title="<?=Loc::getMessage('TASKS_TUFP_LICENSE_RESTRICTED_MANDATORY')?>"<?endif?>>
										<label <?if(!$createMandatory):?>class="disabled"<?endif?>><input class="js-id-item-set-item-required-edit" type="checkbox" <?if(!$createMandatory):?>disabled="disabled"<?endif?>/><?=Loc::getMessage('TASKS_TUFP_FIELD_MANDATORY')?></label>
									</span>
									</div>
									<div class="tasks-uf-panel-form-buttons">
										<button type="button" class="js-id-item-set-item-save ui-btn"><?= Loc::getMessage('TASKS_COMMON_SAVE') ?></button>
										<a class="js-id-item-set-item-cancel ui-btn ui-btn-link" href="javascript:void(0);"><?= Loc::getMessage('TASKS_COMMON_CANCEL') ?></a>
									</div>
									<div class="js-id-item-set-item-error task-message-label error invisible">
									</div>
								</div>
							</div>
						</div>
						<div class="tasks-uf-panel-row-buttons">
							<a href="javascript:void(0);" class="js-id-item-set-item-hide tasks-btn-delete tasks-uf-panel-row-button-delete" title="<?=Loc::getMessage('TASKS_TUFP_FIELD_HIDE')?>"></a>
							<?if($arResult['TEMPLATE_DATA']['CAN_EDIT']):?>
								<a href="javascript:void(0);" class="js-id-item-set-item-edit tasks-btn-edit tasks-uf-panel-row-button-edit" title="<?=Loc::getMessage('TASKS_TUFP_FIELD_EDIT')?>"></a>
							<?endif?>
						</div>
						<div class="tasks-uf-panel-dnd-after"></div>
					</div>
				</div>
				<?$rowTemplate = ob_get_clean();?>

				<div class="js-id-item-set-items js-id-uf-panel-items tasks-uf-panel-items not-empty">

					<div class="tasks-uf-panel-dnd-after panel"></div>

					<?//todo: migrate to <template> tag when get supported?>
					<script data-bx-id="item-set-item" type="text/html">
						<?=$rowTemplate?>
					</script>
					<script data-bx-id="uf-panel-item-flying" type="text/html">
						<div class="tasks-uf-panel tasks flying">
							<div class="tasks-uf-panel-row">
								<div class="tasks-uf-panel-row-geometry">
									<div class="tasks-btn-drag"></div>
									<div class="tasks-uf-panel-row-title">
										<div class="tasks-uf-panel-row-title-text">
											{{LABEL}}
										</div>
									</div>
								</div>
							</div>
						</div>
					</script>
					<script data-bx-id="item-set-item-field-stub" type="text/html">
						<input class="tasks-uf-panel-row-data-field-stub" type="text" data-type="string double datetime" />
						<label class="tasks-uf-panel-row-data-field-stub fields" data-type="boolean">
							<input type="checkbox" />
						</label>
					</script>

					<?// walk by state, because state is pre-sorted ?>
					<?foreach($arResult['DATA']['STATE'] as $id => $state):?>

						<?
						$uf = (
							isset($arResult['TEMPLATE_DATA']['ID2CODE'][$id], $arResult['DATA']['FIELDS'][$arResult['TEMPLATE_DATA']['ID2CODE'][$id]])
								? $arResult['DATA']['FIELDS'][$arResult['TEMPLATE_DATA']['ID2CODE'][$id]]
								: null
						);
						$ufPublic = ($arResult['JS_DATA']['scheme'][$id] ?? null);
						$code = ($uf['CODE'] ?? null);

						if(!$uf || in_array($code, $arParams['EXCLUDE']))
						{
							continue;
						}

						$html = '';
						if($state['D'])
						{
							$uf['FIELD_NAME'] = $arParams['INPUT_PREFIX'].'['.$uf['FIELD_NAME'].']';
							ob_start();
							\Bitrix\Tasks\Util\UserField\UI::showEdit(
								$uf,
								['PREFER_DEFAULT' => !(int)($arParams['DATA']['ID'] ?? null)],
								$this->__component
							);
							$html = ob_get_clean();

							// replace date icon, no ability to do it in other way
							$html = str_replace('/bitrix/js/main/core/images/calendar-icon.gif', '/bitrix/js/tasks/css/images/calendar.png', $html);
						}
						?>

						<?=$arResult['HELPER']->fillTemplate($rowTemplate, array(
							'USER_TYPE_ID' => $ufPublic['USER_TYPE_ID'],
							'MULTIPLE' => $ufPublic['MULTIPLE'] ? '1' : '0',
							'FIELD_HTML' => $html,
							'DISPLAY' => $ufPublic['LABEL'],
							'VALUE' => $ufPublic['ID'],

							// template logic emulation
							'REQUIRED' => $ufPublic['MANDATORY'] ? 'required' : '',
							'EDIT' => '',
							'INVISIBLE' => $state['D'] ? '' : 'invisible',
							'DEFACEABLE' => in_array($ufPublic['USER_TYPE_ID'], $arResult['JS_DATA']['defaceable']) ? 'defaceable': '',
							'DISPLAY_MULTIPLE' => $ufPublic['MULTIPLE'] || $ufPublic['USER_TYPE_ID'] == 'enumeration' ? '1' : '0',
						));?>

					<?endforeach?>
				</div>

				<div class="tasks-uf-panel-new-item-place js-id-item-set-new-item-place js-id-uf-panel-new-item-place">
				</div>

			<?endif?>

			<?//action buttons?>
			<div class="tasks-uf-panel-bottom-actions<?if(!$canUse):?> tasks-uf-panel-bottom-actions-off<?endif?>">

				<?if($canUse):?>
					<?if($arResult['AUX_DATA']['USER']['IS_SUPER'])
					{
						$canUse = $arResult['COMPONENT_DATA']['RESTRICTION']['USE'];
						if (!$canUse)
						{
						?>
							<span
								class="tasks-btn-restricted"
								onclick="<?= Limit::getLimitLockClick(\Bitrix\Tasks\Integration\Bitrix24\FeatureDictionary::TASK_CUSTOM_FIELDS, null)?>"
								style="cursor: pointer;"
							>
								<a class="tasks-uf-panel-btn-action js-id-uf-panel-add-field" href="javascript:void(0);">
									<?=Loc::getMessage('TASKS_TUFP_FIELD_ADD')?>
								</a>
							</span>
						<?php
						}
						else
						{
						?>
							<span>
								<a class="tasks-uf-panel-btn-action js-id-uf-panel-add-field" href="javascript:void(0);">
									<?=Loc::getMessage('TASKS_TUFP_FIELD_ADD')?>
								</a>
							</span>
						<?php
						}
					}?>
					<a class="js-id-uf-panel-un-hide-field" href="javascript:void(0);"><?=Loc::getMessage('TASKS_TUFP_FIELD_UN_HIDE')?></a>
				<?else:?>
					<?=Loc::getMessage('TASKS_TUFP_NO_FIELDS_TO_SHOW');?>
				<?endif?>

			</div>

			<?// contents for un-hide-item popup?>
			<div class="js-id-uf-panel-un-hide-menu no-display">
				<div class="js-id-scrollpane-pane menu-popup tasks-uf-panel-scrollpane tasks-scrollpane">
					<div class="js-id-scrollpane-body js-id-uf-panel-uhmenu menu-popup-items tasks-scrollpane-body">
						<script data-bx-id="uf-panel-menu-item" type="text/html">
							<span title="{{LABEL_EXT}}" data-id="{{ID}}" class="js-id-scrollpane-item menu-popup-item menu-popup-no-icon">
						<span class="menu-popup-item-text">
							<span class="tasks-red {{STAR_INVISIBLE}}">*</span>&nbsp;{{LABEL}}
						</span>
					</span>
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?$arResult['HELPER']->initializeExtension();?>
<?endif?>
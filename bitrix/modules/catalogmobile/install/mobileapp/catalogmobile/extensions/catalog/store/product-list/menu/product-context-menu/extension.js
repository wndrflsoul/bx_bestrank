/**
 * @module catalog/store/product-list/menu/product-context-menu
 */
jn.define('catalog/store/product-list/menu/product-context-menu', (require, exports, module) => {
	const { ContextMenu } = require('layout/ui/context-menu');
	/**
	 * @class StoreDocumentProductContextMenu
	 */
	class StoreDocumentProductContextMenu
	{
		constructor(props)
		{
			this.props = props || {};
			this.items = this.buildItems();
			this.menuInstance = new ContextMenu({
				actions: this.items,
				params: {
					showCancelButton: true,
				}
			});
		}

		buildItems()
		{
			let actions;
			if (this.props.editable)
			{
				actions = [
					{
						id: 'edit',
						title: BX.message('CSPL_PRODUCT_CONTEXT_MENU_EDIT'),
						subTitle: '',
						data: {
							svgIcon: SvgIcons.edit.content
						},
						onClickCallback: this.callback.bind(this, 'onChooseEdit'),
					},
				];

				if (this.props.onChooseChangeVariation)
				{
					actions.push({
						id: 'changeVariation',
						title: BX.message('CSPL_PRODUCT_CONTEXT_MENU_CHANGE_VARIATION'),
						subTitle: '',
						data: {
							svgIcon: SvgIcons.changeVariation.content
						},
						onClickCallback: this.callback.bind(this, 'onChooseChangeVariation'),
					});
				}

				actions.push({
					id: 'remove',
					type: 'delete',
					title: BX.message('CSPL_PRODUCT_CONTEXT_MENU_REMOVE_MSGVER_1'),
					subTitle: '',
					onClickCallback: this.callback.bind(this, 'onChooseRemove'),
				});
			}
			else
			{
				actions = [{
					id: 'open',
					title: BX.message('CSPL_PRODUCT_CONTEXT_MENU_OPEN'),
					subTitle: '',
					data: {
						svgIcon: SvgIcons.open.content
					},
					onClickCallback: this.callback.bind(this, 'onChooseOpen'),
				}];
			}

			return actions;
		}

		callback(eventName)
		{
			this.menuInstance.close(() => {
				if (this.props[eventName])
				{
					this.props[eventName]();
				}
			});
			return Promise.resolve();
		}

		show()
		{
			this.menuInstance.show();
		}
	}

	const SvgIcons = {
		edit: {
			content: `<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.7425 6.25L23.75 10.2997L12.1325 21.875L8.125 17.8253L19.7425 6.25ZM6.26396 23.2285C6.22606 23.3719 6.26667 23.5234 6.36953 23.629C6.47509 23.7345 6.62668 23.7751 6.77014 23.7345L11.25 22.5276L7.47122 18.75L6.26396 23.2285Z" fill="#525C69"/></svg>`
		},
		changeVariation: {
			content: `<svg width="31" height="30" viewBox="0 0 31 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.3757 6.1145C15.3903 6.10691 15.4045 6.09956 15.4181 6.09541C15.4706 6.08034 15.5154 6.09227 15.5724 6.12134L23.9264 9.42799C24.1408 9.52702 24.221 9.71647 24.2164 10.0394V10.3885H21.6241L15.4673 7.94442L8.99995 10.5014L15.2976 12.9914V24.7899C15.2925 24.7879 15.2874 24.7858 15.2825 24.7836L7.05171 21.5287C6.8882 21.4653 6.76403 21.2403 6.76172 21.0035V9.97052C6.7679 9.72081 6.84348 9.50876 7.05171 9.41081L15.3626 6.12124L15.3757 6.1145ZM18.6811 13.8255C18.405 13.8255 18.1811 14.0494 18.1811 14.3255V15.4281C18.1811 15.7043 18.405 15.9281 18.6811 15.9281H29.0833C29.3594 15.9281 29.5833 15.7043 29.5833 15.4281V14.3255C29.5833 14.0494 29.3594 13.8255 29.0833 13.8255H18.6811ZM18.6811 18.0317C18.405 18.0317 18.1811 18.2556 18.1811 18.5317V19.6344C18.1811 19.9105 18.405 20.1344 18.6811 20.1344H29.0833C29.3594 20.1344 29.5833 19.9105 29.5833 19.6344V18.5317C29.5833 18.2556 29.3594 18.0317 29.0833 18.0317H18.6811ZM18.1811 22.738C18.1811 22.4618 18.405 22.238 18.6811 22.238H29.0833C29.3594 22.238 29.5833 22.4618 29.5833 22.738V23.8406C29.5833 24.1167 29.3594 24.3406 29.0833 24.3406H18.6811C18.405 24.3406 18.1811 24.1167 18.1811 23.8406V22.738Z" fill="#767C87"/></svg>`
		},
		open: {
			content: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92308 0.10083C8.90652 0.105853 8.88925 0.1159 8.87125 0.124941L1.11441 3.19521C0.920058 3.28663 0.849518 3.48454 0.84375 3.7176V13.6032C0.84591 13.8243 0.9618 14.0342 1.11441 14.0935L8.79643 17.1313C8.89145 17.1735 9.01598 17.1675 9.11891 17.1394L16.8699 14.0774C17.0225 14.0151 17.1362 13.8011 17.1347 13.5791V3.78188C17.1391 3.48049 17.0642 3.30367 16.8641 3.21124L9.06708 0.125031C9.01381 0.0979062 8.97202 0.086765 8.92308 0.10083ZM8.96914 1.15369L15.4073 3.70946L8.96914 6.24915L2.52526 3.70143L8.96914 1.15369Z" fill="#bdc1c6"/></svg>`
		}
	};

	module.exports = { StoreDocumentProductContextMenu };
});

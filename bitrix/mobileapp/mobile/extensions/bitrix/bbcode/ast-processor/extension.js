/**
 * Attention!
 * This file is generated automatically from the extension `ui.bbcode.ast-processor`.
 * Any manual changes to this file are not allowed.
 *
 * If you need to make some changes, then make edits to the `ui.bbcode.ast-processor`
 * and run the build using 'bitrix build'.
 * During the build, the code will be automatically ported to `bbcode/ast-processor'.
 */

/** @module bbcode/ast-processor */
jn.define('bbcode/ast-processor', (require, exports, module) => {
	const {Type} = require('type');

	function getByIndex(array, index) {
	  if (!Type.isArray(array)) {
	    throw new TypeError('array is not a array');
	  }
	  if (!Type.isInteger(index)) {
	    throw new TypeError('index is not a integer');
	  }
	  const preparedIndex = index < 0 ? array.length + index : index;
	  return array[preparedIndex];
	}

	class AstProcessor {
	  /**
	   * Makes flat list from AST
	   */
	  static flattenAst(ast) {
	    if (ast && ast.getChildren) {
	      const children = ast.getChildren();
	      return [...children, ...children.flatMap(node => {
	        return AstProcessor.flattenAst(node);
	      })];
	    }
	    return [];
	  }

	  /**
	   * Parses selector
	   */
	  static parseSelector(selector) {
	    const regex = /(\w+)\[(.*?)]|\s*(>)\s*|\w+/g;
	    const matches = [...selector.matchAll(regex)];
	    return matches.map(([fullMatch, nodeName, rawProps, arrow]) => {
	      if (arrow) {
	        return '>';
	      }
	      if (rawProps) {
	        const propsRegexp = /(\w+)=["'](.*?)["']/g;
	        const propsMatches = [...rawProps.matchAll(propsRegexp)];
	        const props = propsMatches.map(([, key, value]) => {
	          return [key, value];
	        });
	        return {
	          nodeName,
	          props
	        };
	      }
	      return {
	        nodeName: fullMatch,
	        props: []
	      };
	    });
	  }

	  /**
	   * @private
	   */
	  static matchesNodeWithSelector(node, selector) {
	    if (node && node.constructor.name === selector.nodeName) {
	      if (selector.props.length > 0) {
	        return selector.props.every(([key, value]) => {
	          const propValue = (() => {
	            const name = `${key.charAt(0).toUpperCase()}${key.slice(1)}`;
	            if (Type.isFunction(node[`get${name}`])) {
	              return node[`get${name}`]();
	            }
	            if (Type.isFunction(node[`is${name}`])) {
	              return node[`is${name}`]();
	            }
	            return null;
	          })();
	          if (['true', 'false'].includes(value)) {
	            return propValue === (value === 'true');
	          }
	          return propValue === value;
	        });
	      }
	      return true;
	    }
	    return false;
	  }

	  /**
	   * Finds parent node by parsed selector
	   */
	  static findParentNode(node, selector) {
	    if (node) {
	      const preparedSelector = (() => {
	        if (Type.isStringFilled(selector)) {
	          return AstProcessor.parseSelector(selector)[0];
	        }
	        return selector;
	      })();
	      const parent = node.getParent();
	      if (AstProcessor.matchesNodeWithSelector(parent, preparedSelector)) {
	        return parent;
	      }
	      return AstProcessor.findParentNode(parent, preparedSelector);
	    }
	    return null;
	  }
	  static findParentNodeByName(node, name) {
	    if (node) {
	      const parent = node.getParent();
	      if (parent && parent.getName() === name) {
	        return parent;
	      }
	      return AstProcessor.findParentNodeByName(parent, name);
	    }
	    return null;
	  }

	  /**
	   * Find elements by selector
	   */
	  static findElements(ast, selector) {
	    const flattenedAst = AstProcessor.flattenAst(ast);
	    const parsedSelector = AstProcessor.parseSelector(selector);
	    const lastSelector = getByIndex(parsedSelector, -1);
	    let checkClosestParent = false;
	    return parsedSelector.reduceRight((acc, currentSelector) => {
	      if (Type.isPlainObject(currentSelector)) {
	        if (currentSelector === lastSelector) {
	          return acc.filter(node => {
	            return AstProcessor.matchesNodeWithSelector(node, currentSelector);
	          });
	        }
	        if (checkClosestParent) {
	          checkClosestParent = false;
	          return acc.filter(node => {
	            return AstProcessor.matchesNodeWithSelector(node.getParent(), currentSelector);
	          });
	        }
	        return acc.filter(node => {
	          return AstProcessor.findParentNode(node, currentSelector) !== null;
	        });
	      }
	      if (currentSelector === '>') {
	        checkClosestParent = true;
	      }
	      return acc;
	    }, flattenedAst);
	  }

	  /**
	   * Reduces AST
	   */
	  static reduceAst(ast, reducer) {
	    const children = ast.getChildren == null ? void 0 : ast.getChildren().reduce((acc, child) => {
	      const preparedChild = [AstProcessor.reduceAst(child, reducer)].flat();
	      if (!Type.isNil(preparedChild)) {
	        acc.replaceChild(child, ...preparedChild);
	      }
	      return acc;
	    }, ast);
	    return reducer(ast, children);
	  }
	}

	exports.AstProcessor = AstProcessor;

});
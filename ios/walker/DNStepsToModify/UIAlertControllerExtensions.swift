//
//  UIAlertControllerExtensions.swift
//  Walker
//
//  Created by levil on 2017/3/2.
//  Copyright © 2017年 levil. All rights reserved.
//

import UIKit

extension UIAlertController {
    
    // 创建一个默认提示框
    public convenience init(title: String, message: String? = nil, defaultActionButtonTitle: String = "确定", tintColor: UIColor? = nil) {
        self.init(title: title, message: message, preferredStyle: .alert)
        let defaultAction = UIAlertAction(title: defaultActionButtonTitle, style: .default, handler: nil)
        self.addAction(defaultAction)
        if let color = tintColor {
            self.view.tintColor = color
        }
    }
    
    // 创建一个错误提示框
    public convenience init(title: String = "错误", error: Error, defaultActionButtonTitle: String = "确定", tintColor: UIColor? = nil) {
        self.init(title: title, message: error.localizedDescription, preferredStyle: .alert)
        let defaultAction = UIAlertAction(title: defaultActionButtonTitle, style: .default, handler: nil)
        self.addAction(defaultAction)
        if let color = tintColor {
            self.view.tintColor = color
        }
    }
    
    // 显示提示框
    public func show(vibrate: Bool = false) {
        UIApplication.shared.keyWindow?.rootViewController?.present(self, animated: true, completion: nil)
    }
    
    // 创建一个提示框 有回调
    func addAction(title: String, style: UIAlertActionStyle = .default, isEnabled: Bool = true, handler: ((UIAlertAction) -> Void)? = nil) -> UIAlertAction {
        let action = UIAlertAction(title: title, style: style, handler: handler)
        action.isEnabled = isEnabled
        self.addAction(action)
        return action
    }
    
    // 添加一个文本输入提示框
    func addTextField(text: String? = nil, placeholder: String? = nil, editingChangedTarget: Any?, editingChangedSelector: Selector? = nil) {
        addTextField { tf in
            tf.text = text
            tf.placeholder = placeholder
            if let target = editingChangedTarget, let selector = editingChangedSelector {
                tf.addTarget(target, action: selector, for: .editingChanged)
            }
        }
    }
    
}

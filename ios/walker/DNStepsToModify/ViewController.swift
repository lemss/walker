//
//  ViewController.swift
//  Walker
//
//  Created by levil on 2017/3/2.
//  Copyright © 2017年 levil. All rights reserved.
//

import UIKit
import HealthKit

class ViewController: UIViewController, UIWebViewDelegate, UITabBarDelegate {
        
    // 定义健康中心
    let healthStore: HKHealthStore? = {
        if HKHealthStore.isHealthDataAvailable() {
            return HKHealthStore()
        } else {
            return nil
        }
    }()
    @IBOutlet var webView: UIWebView!
    
    @IBOutlet var tabBar: UITabBar!
    @IBOutlet weak var stepsNumberLabel: UILabel!
    @IBOutlet weak var addStepsTextField: UITextField!
    
    let mainUrl = "http://10.70.32.59/website/index"
    let traceUrl = "http://10.70.32.59/trace.html"
    let rankUrl = "http://10.70.32.59/website/rank"
    let aboutUrl = "http://10.70.32.59/website/about"
    var uploadUrl = "http://10.70.32.59/userdata/upload"
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        // 初始化后需要做的工作
        // webView大小自适应
        self.webView.frame = CGRect(x: 0, y: 20, width: UIScreen.main.bounds.size.width, height: UIScreen.main.bounds.size.height - 65)
        // 获取步数信息
        self.refreshSteps()
        webView.loadRequest(URLRequest(url:URL(string: mainUrl)!))
        self.webView.delegate = self
        self.tabBar.delegate = self
    }
    
    override func viewWillAppear(_ animated: Bool) {
        super.viewWillAppear(animated)
        self.refreshSteps()
    }
    
    // 响应webView页面加载完成事件
    func webViewDidFinishLoad(_ webView: UIWebView) {
        // 防止方法多次调用
        if (webView.isLoading) {
            return;
        }
        self.refreshSteps()
    }
    
    // 响应tabBar select事件
    func tabBar(_ tabBar: UITabBar, didSelect item: UITabBarItem) {
        let itemNumber = tabBar.selectedItem?.tag
        if itemNumber == 1 {
            webView.loadRequest(URLRequest(url:URL(string: mainUrl)!))
        } else if itemNumber == 2 {
            webView.loadRequest(URLRequest(url:URL(string: traceUrl)!))
        } else if itemNumber == 3 {
            webView.loadRequest(URLRequest(url:URL(string: aboutUrl)!))
        } else {
            webView.loadRequest(URLRequest(url:URL(string: rankUrl)!))
        }
    }
    
    // 刷新步数，上送数据
    func refreshSteps() {
        // 弹出请求权限，这里只获取读取和写入步数的权限
        let stepType: HKQuantityType? = HKObjectType.quantityType(forIdentifier: .stepCount)
        let dataTypesToRead = NSSet(objects: stepType as Any)

        // 权限请求
        self.healthStore?.requestAuthorization(toShare: dataTypesToRead as? Set<HKSampleType>, read: dataTypesToRead as? Set<HKObjectType>, completion: { [unowned self] (success, error) in

            // 得到权限后就可以获取和写入步数了
            if success {
                self.fetchSumOfSamplesToday(for: stepType!, unit: HKUnit.count()) { (stepCount, error) in
                    // 获取到步数后,在主线程中更新数字
                    DispatchQueue.main.async {
                        self.stepsNumberLabel.text = "今日步数: \(Int(stepCount!))"
                        // 如果从源码中获取到userid，上送步数信息
                        let userid = self.webView.stringByEvaluatingJavaScript(from: "document.getElementsByClassName('userid')[0].innerHTML")
                        //let userid = "123"
                        if userid != "" {
                            let w = Wings()
                            // 获取当前时间
                            let now = Date()
                            let dateFormatter = DateFormatter()
                            dateFormatter.dateFormat = "yyyy-MM-dd"
                            let day = dateFormatter.string(from: now)
                            // 上送数据
                            self.uploadUrl = self.uploadUrl + "?uid=" + userid! + "&day=" + day + "&distance=" + String(Int(stepCount!)) + "&token=4aa4e923a4006e3b"
                            print(self.uploadUrl)
                            if let response = w.get(url: self.uploadUrl, headers:nil) {
                                print(response.text ?? "")
                            }
                        }
                    }
                }
            } else {
                let alert = UIAlertController(title: "提示", message: "不给权限我怎么获取到数据呢，赶紧去设置界面打开权限吧", defaultActionButtonTitle: "确定", tintColor: UIColor.red)
                alert.show()
            }
        })
    }
    
    
    // 添加数据
    @IBAction func addStepsBtnClick(_ sender: UIButton) {
        if let num = addStepsTextField.text {
            self.addstep(withStepNum: Double(num)!)
        }
    }
    
    func fetchSumOfSamplesToday(for quantityType: HKQuantityType, unit: HKUnit, completion completionHandler: @escaping (Double?, NSError?)->()) {
        let predicate: NSPredicate? = self.predicateForSamplesToday()
        
        let query = HKStatisticsQuery(quantityType: quantityType, quantitySamplePredicate: predicate, options: .cumulativeSum) { query, result, error in
            
                var totalCalories = 0.0
                if let quantity = result?.sumQuantity() {
                    let unit = HKUnit.count()
                    totalCalories = quantity.doubleValue(for: unit)
                }
                completionHandler(totalCalories, error as NSError?)
        }

        self.healthStore?.execute(query)
    }
    
    func predicateForSamplesToday() -> NSPredicate {
        let calendar = Calendar.current
        let now = Date()
        let startDate: Date? = calendar.startOfDay(for: now)
        let endDate: Date? = calendar.date(byAdding: .day, value: 1, to: startDate!)
        return HKQuery.predicateForSamples(withStart: startDate, end: endDate, options: .strictStartDate)
    }
    
    func addstep(withStepNum stepNum: Double) {
        let stepCorrelationItem: HKQuantitySample? = self.stepCorrelation(withStepNum: stepNum)
        self.healthStore?.save(stepCorrelationItem!, withCompletion: { (success, error) in
            DispatchQueue.main.async(execute: {() -> Void in
                if success {
                    self.view.endEditing(true)
                    self.addStepsTextField.text = ""
                    self.refreshSteps()
                    let alert = UIAlertController(title: "提示", message: "添加步数成功", defaultActionButtonTitle: "确定", tintColor: UIColor.red)
                    alert.show()
                } else {
                    let alert = UIAlertController(title: "提示", message: "添加步数失败", defaultActionButtonTitle: "确定", tintColor: UIColor.red)
                    alert.show()
                    return
                }
            })
        })
    }
    
    func stepCorrelation(withStepNum stepNum: Double) -> HKQuantitySample {
        let endDate = Date()
        let startDate = Date(timeInterval: -300, since: endDate)
        let stepQuantityConsumed = HKQuantity(unit: HKUnit.count(), doubleValue: stepNum)
        let stepConsumedType = HKQuantityType.quantityType(forIdentifier: .stepCount)
        let stepConsumedSample = HKQuantitySample(type: stepConsumedType!, quantity: stepQuantityConsumed, start: startDate, end: endDate, metadata: nil)
        return stepConsumedSample
    }
    
    override func touchesBegan(_ touches: Set<UITouch>, with event: UIEvent?) {
        super.touchesBegan(touches, with: event)
        self.view.endEditing(true)
    }

}


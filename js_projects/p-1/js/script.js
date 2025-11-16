
// ------------project-----------------
let users = [
    {  
        userId : 1,   
        userName: "Nouran",
        userBalance: 2000,
    },
    { 
        userId : 2,
        userName: "Marwa",
        userBalance: 3000,
    }
]

function addUser(){
    let newUser = {
        userId: parseInt(prompt("Enter user ID")), // تحويل إلى رقم
        userName: prompt("Enter user first Name"),
        userBalance: parseFloat(prompt("Enter user Balance")) // تحويل إلى رقم
    }

    users.push(newUser);
    console.log(users);
}
// addUser()

// -----------------
function editUserBalanceByID(){
    let id = parseInt(prompt("Enter user ID to edit balance"));
    let newBalance = parseFloat(prompt("Enter new balance"));
    
    let user = users.find(user => user.userId === id);
    
    if(user) {
        user.userBalance = newBalance;
        console.log("Balance updated successfully");
        console.log(users);
    } else {
        console.log("User not found");
    }
}

// ----------------
function deleteUserById(){
    let id = parseInt(prompt("Enter user ID to delete user"));
    
    // البحث عن index المستخدم
    let userIndex = users.findIndex(user => user.userId === id);
    
    if(userIndex !== -1) {
        // حذف المستخدم من المصفوفة
        users.splice(userIndex, 1);
        console.log("User deleted successfully");
        console.log(users);
    } else {
        console.log("User not found");
    }
}

// استدعاء الدالة
// deleteUserById();
// استدعاء الدالة لتعديل الرصيد
// editUserBalanceByID();



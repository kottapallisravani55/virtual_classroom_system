import java.util.*;
class lucky{
public static void verify(String num){
int i,count=0,flag=0,flag1=0;
int len=num.length();
for(i=0;i<len;i++){
if(num.charAt(i)==num.charAt(i+1)){
count++;
if(count==5 ){
flag=1;
}
}
if(num.charAt(i+1)==num.charAt(i)+1){
flag1=1;
}
}
if( flag1==0){
System.out.println("given number is  not lucky number");
}
else{
System.out.println("given number is   lucky number");
}
}
public static void main(String []args){
Scanner sc=new Scanner(System.in);
System.out.println("enter phnum:");
String phnum=sc.next();
verify(phnum);
}
}
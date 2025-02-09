import java.util.*;
class Test extends Exception{
static void duplicate(int arr[],int size)throws Exception{
int i;
for(i=0;i<size;i++){
if(arr[i]==arr[i+1]){
throw new Exception("Duplicate number exception");
}
}
}
static void negative(int arr[],int size)throws Exception{
int i;
for(i=0;i<size;i++){
if(arr[i]<0&&arr[i]!=-1){
throw new Exception("negative number Exception");
}
}
}
static void prime(int arr[],int size) throws  Exception{
int i,j,count=0;
for(i=2;i<size;i++){
for(j=0;j<size;j++){
if(i%j==0){
count++;
}
}
}
if(count==2){
throw new Exception ("prime number Exception");
}
}
static void count(int arr[],int size){
int i,count_odd=0,count_even=0;
for (i=0;i<size;i++){
if(arr[i]!=-1){
if(arr[i]%2==0){
count_even++;
}
else{
count_odd++;
}
}
else{
System.out.println("odd numbers:"+count_odd+"even  numbers"+count_even);
}
}
}
public  static void main(String [] args){
Scanner sc=new Scanner(System.in);
System.out.println("enter size:");
int size=sc.nextInt();
int arr[]=new int[size];
int i;

try{
for( i=0;i<size;i++)
{
arr[i]=sc.nextInt();
}
duplicate(arr,size);
negative(arr,size);
prime(arr,size);
}

catch(Exception e){
e.getMessage();
}

for(i=0;i<size;i++)
{
if(arr[i]==-1){
System.out.println("Exit..");
break;
}
}
count(arr,size);

}
}